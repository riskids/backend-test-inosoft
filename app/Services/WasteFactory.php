<?php

namespace App\Services;

use App\Models\Waste;
use InvalidArgumentException;

class WasteFactory
{
    public function make(string $type, array $attributes): Waste
    {
        $class = Waste::TYPE_MAP[$type] ?? throw new InvalidArgumentException("Unknown waste type: {$type}");
        
        return new $class(array_merge($attributes, ['type' => $type]));
    }
}
