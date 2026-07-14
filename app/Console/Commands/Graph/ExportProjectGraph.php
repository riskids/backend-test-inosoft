<?php

namespace App\Console\Commands\Graph;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ExportProjectGraph extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graph:export {--output=storage/app/graph/project_map.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export project structure as a JSON graph for AI context understanding';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting project graph export...');

        $outputPath = $this->option('output');
        if (!str_starts_with($outputPath, '/')) {
            $outputPath = base_path($outputPath);
        }

        // Ensure directory exists
        $directory = dirname($outputPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $graph = [
            'metadata' => [
                'project_name' => config('app.name', 'Laravel'),
                'exported_at' => now()->toIso8601String(),
                'git_commit' => $this->getGitCommit(),
            ],
            'nodes' => [],
            'edges' => [],
        ];

        $appPath = app_path();
        $this->info("Scanning {$appPath}...");

        // Scan all PHP files in app/ directory
        $files = $this->getAllPhpFiles($appPath);

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $relativePath = str_replace($appPath . '/', '', $file);
            $node = $this->parseFile($file, $relativePath);

            if ($node) {
                $graph['nodes'][] = $node;

                // Create edges based on dependencies
                foreach ($node['dependencies'] as $dependency) {
                    $graph['edges'][] = [
                        'source' => $node['id'],
                        'target' => $dependency,
                        'type' => 'depends_on',
                    ];
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Add route-to-controller edges
        $this->info('Analyzing routes...');
        $routeEdges = $this->analyzeRoutes();
        foreach ($routeEdges as $edge) {
            $graph['edges'][] = $edge;
        }

        // Write to file
        File::put($outputPath, json_encode($graph, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Graph exported to {$outputPath}");
        $this->info("Total nodes: " . count($graph['nodes']));
        $this->info("Total edges: " . count($graph['edges']));

        return Command::SUCCESS;
    }

    /**
     * Get all PHP files in directory
     */
    private function getAllPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Parse a PHP file and extract node information
     */
    private function parseFile(string $filePath, string $relativePath): ?array
    {
        $content = File::get($filePath);

        // Extract namespace
        preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches);
        $namespace = $namespaceMatches[1] ?? '';

        // Extract class/interface/trait name
        preg_match('/(?:class|interface|trait|enum)\s+(\w+)/', $content, $nameMatches);
        $name = $nameMatches[1] ?? null;

        if (!$name) {
            return null;
        }

        // Determine type
        $type = $this->determineType($relativePath, $content);

        // Extract dependencies (use statements and type-hinted classes)
        $dependencies = $this->extractDependencies($content, $namespace);

        // Extract relationships
        $relationships = $this->extractRelationships($content);

        return [
            'id' => $namespace . '\\' . $name,
            'name' => $name,
            'namespace' => $namespace,
            'type' => $type,
            'file' => $relativePath,
            'dependencies' => $dependencies,
            'relationships' => $relationships,
        ];
    }

    /**
     * Determine the type of file based on path and content
     */
    private function determineType(string $relativePath, string $content): string
    {
        if (str_contains($relativePath, '/Models/')) {
            return 'model';
        }
        if (str_contains($relativePath, '/Controllers/')) {
            return 'controller';
        }
        if (str_contains($relativePath, '/Services/')) {
            return 'service';
        }
        if (str_contains($relativePath, '/Repositories/Contracts/')) {
            return 'repository_interface';
        }
        if (str_contains($relativePath, '/Repositories/Eloquent/')) {
            return 'repository';
        }
        if (str_contains($relativePath, '/Requests/')) {
            return 'request';
        }
        if (str_contains($relativePath, '/Resources/')) {
            return 'resource';
        }
        if (str_contains($relativePath, '/Providers/')) {
            return 'provider';
        }
        if (str_contains($relativePath, '/Exceptions/')) {
            return 'exception';
        }
        if (str_contains($relativePath, '/Commands/')) {
            return 'command';
        }

        // Check content for patterns
        if (str_contains($content, 'extends Model')) {
            return 'model';
        }
        if (str_contains($content, 'extends Controller')) {
            return 'controller';
        }
        if (str_contains($content, 'interface ')) {
            return 'interface';
        }

        return 'class';
    }

    /**
     * Extract dependencies from file content
     */
    private function extractDependencies(string $content, string $currentNamespace): array
    {
        $dependencies = [];

        // Extract use statements
        preg_match_all('/^use\s+([^;]+);/m', $content, $useMatches);
        foreach ($useMatches[1] as $use) {
            $use = trim($use);
            // Skip function imports and aliases
            if (!str_starts_with($use, 'function ') && !str_contains($use, ' as ')) {
                $dependencies[] = $use;
            }
        }

        // Extract type-hinted dependencies in constructor and methods
        preg_match_all('/(?:public|protected|private|function)\s+function\s+\w+\s*\([^)]*\)/', $content, $methodMatches);
        foreach ($methodMatches[0] as $method) {
            preg_match_all('/(?<!\$)\b([A-Z][a-zA-Z0-9_\\\\]*(?:\\\\[A-Z][a-zA-Z0-9_]*)*)\b/', $method, $typeMatches);
            foreach ($typeMatches[1] as $type) {
                if (!in_array($type, ['Request', 'Response', 'Collection', 'Model', 'JsonResource'])) {
                    $fullType = $this->resolveType($type, $currentNamespace, $content);
                    if ($fullType && !in_array($fullType, $dependencies)) {
                        $dependencies[] = $fullType;
                    }
                }
            }
        }

        // Extract property type hints
        preg_match_all('/(?:public|protected|private)\s+(?:static\s+)?([A-Z][a-zA-Z0-9_\\\\]*(?:\\\\[A-Z][a-zA-Z0-9_]*)*)\s+\$/m', $content, $propertyMatches);
        foreach ($propertyMatches[1] as $type) {
            $fullType = $this->resolveType($type, $currentNamespace, $content);
            if ($fullType && !in_array($fullType, $dependencies)) {
                $dependencies[] = $fullType;
            }
        }

        return $dependencies;
    }

    /**
     * Resolve a type to its full namespace
     */
    private function resolveType(string $type, string $currentNamespace, string $content): ?string
    {
        // Check if already fully qualified
        if (str_starts_with($type, '\\')) {
            return substr($type, 1);
        }

        // Check use statements
        preg_match_all('/^use\s+([^;]+)\s+as\s+' . preg_quote($type) . ';$/m', $content, $aliasMatches);
        if (!empty($aliasMatches[1])) {
            return trim($aliasMatches[1][0]);
        }

        // Check if in current namespace
        if ($currentNamespace) {
            return $currentNamespace . '\\' . $type;
        }

        return $type;
    }

    /**
     * Extract relationships (extends, implements)
     */
    private function extractRelationships(string $content): array
    {
        $relationships = [];

        // Extract extends
        if (preg_match('/(?:class|enum)\s+\w+\s+extends\s+([A-Z][a-zA-Z0-9_\\\\]*)/', $content, $extendsMatches)) {
            $relationships['extends'] = $extendsMatches[1];
        }

        // Extract implements
        if (preg_match('/(?:class|enum)\s+\w+\s+implements\s+([A-Z][a-zA-Z0-9_\\\\\s,]*)/', $content, $implementsMatches)) {
            $interfaces = preg_split('/\s*,\s*/', trim($implementsMatches[1]));
            $relationships['implements'] = array_filter($interfaces);
        }

        return $relationships;
    }

    /**
     * Analyze routes and create edges
     */
    private function analyzeRoutes(): array
    {
        $edges = [];
        $routeFiles = ['routes/api.php', 'routes/web.php', 'routes/console.php'];

        foreach ($routeFiles as $routeFile) {
            if (File::exists(base_path($routeFile))) {
                $content = File::get(base_path($routeFile));

                // Extract controller references
                preg_match_all('/\[([\w\\\\]+)::class,\s*[\'"](\w+)[\'"]\]/', $content, $routeMatches);

                foreach ($routeMatches[1] as $index => $controller) {
                    $action = $routeMatches[2][$index];
                    $controllerId = 'App\\Http\\Controllers\\' . str_replace('/', '\\', $controller);

                    $edges[] = [
                        'source' => 'route:' . $routeFile . ':' . $action,
                        'target' => $controllerId,
                        'type' => 'routes_to',
                        'action' => $action,
                    ];
                }
            }
        }

        return $edges;
    }

    /**
     * Get current git commit hash
     */
    private function getGitCommit(): ?string
    {
        try {
            return trim(shell_exec('git rev-parse HEAD 2>/dev/null')) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
