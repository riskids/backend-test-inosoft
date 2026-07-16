# Community Waste Collection API

A Laravel 13 + MongoDB REST API for managing community waste collection, developed as a backend technical test for PT Inosoft Trans Sistem.

## Requirements

- PHP 8.3+ (8.4 recommended)
- Laravel 13.x
- MongoDB Server 8.0+
- `mongodb` PHP extension
- Composer

## Setup

### 1. Clone and Install Dependencies

```bash
composer install
```

### 2. Configure Environment

Copy `.env.example` to `.env` and update:

```env
MONGODB_URI=mongodb://localhost:27017
MONGODB_DATABASE=waste_collection
```

Or use Docker (see below).

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Seed Database (Optional)

```bash
php artisan db:seed
```

This creates sample households with mixed waste types and payments for testing.

### 5. Start Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api/v1`

## Docker Alternative

```bash
docker compose up -d --build
```

This starts:
- `app` - PHP-FPM 8.4 with Laravel
- `nginx` - Web server on port 8000
- `mongo` - MongoDB 8.0

## API Endpoints

### Base URL

```
http://localhost:8000/api/v1
```

### Households

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/households` | Create household |
| GET | `/households` | List households (with filters) |
| GET | `/households/{id}` | Get household |
| PUT | `/households/{id}` | Update household |
| DELETE | `/households/{id}` | Soft delete household |

### Pickups (Waste)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/pickups` | Create pickup |
| PUT | `/pickups/{id}/schedule` | Schedule pickup |
| PUT | `/pickups/{id}/complete` | Complete pickup |
| PUT | `/pickups/{id}/cancel` | Cancel pickup |

### Payments

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/payments` | Create manual payment |
| GET | `/payments` | List payments |
| PUT | `/payments/{id}/confirm` | Confirm payment |

### Reports

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/reports/waste-summary` | Waste statistics by type/status |
| GET | `/reports/payment-summary` | Payment statistics |
| GET | `/reports/households/{id}/history` | Household pickup/payment history |

## Business Rules

1. **No new pickup if household has unpaid payment**
   - Returns: `422 UnpaidPaymentExistsException`

2. **Pickup can only be scheduled if status = pending**
   - Returns: `409 InvalidPickupStatusException`

3. **Completing a pickup auto-generates a payment**
   - Organic/Plastic/Paper: 50,000 IDR
   - Electronic: 100,000 IDR

4. **Electronic waste requires safety check before scheduling**
   - Returns: `422 SafetyCheckRequiredException`

5. **Organic waste auto-cancels after 3 days**
   - Handled by `AutoCancelStaleWaste` scheduled command

## Response Format

All responses follow this envelope:

```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

Error responses:

```json
{
    "success": false,
    "message": "Error description",
    "errors": null
}
```

## Testing

Run all tests:

```bash
php artisan test
```

Run specific test file:

```bash
php artisan test --filter=PickupApiTest
```

### Test Categories

- **Unit Tests**: `tests/Unit/WasteServiceTest.php`, `tests/Unit/WasteRepositoryTest.php`
- **Feature Tests**: `tests/Feature/HouseholdApiTest.php`, `tests/Feature/PickupApiTest.php`, `tests/Feature/PaymentApiTest.php`, `tests/Feature/ReportApiTest.php`

## Postman Collection

Import `postman/Waste-Collection-API.postman_collection.json` into Postman to test all endpoints.

The collection includes:
- All CRUD operations
- Business rule test cases (success + failure scenarios)
- Pre-request scripts for automatic ID extraction

## Design Notes

### Polymorphism Approach

The project uses a **type discriminator pattern** for waste types:

- Single `wastes` MongoDB collection with a `type` field
- Base `Waste` model overrides `newFromBuilder()` to hydrate the correct subclass
- Each subclass (`WasteOrganic`, `WasteElectronic`, etc.) defines polymorphic methods:
  - `completionAmount()` - Payment amount on completion
  - `requiresPreScheduleCheck()` - Whether safety check is required
  - `autoCancelAfterDays()` - Days before auto-cancel (organic only)

This allows `WasteService` to work with any waste type **without switch/if statements on type**.

### Repository Pattern

All data access goes through interfaces (`*RepositoryInterface`) implemented by Eloquent classes. This:
- Decouples business logic from persistence
- Enables unit testing via mocking
- Provides a clean abstraction over MongoDB

### Custom Exceptions

Domain exceptions are in `App\Exceptions\Domain\*` and are automatically converted to HTTP responses in `bootstrap/app.php`.

## Project Structure

```
app/
├── Console/Commands/        # Artisan commands (AutoCancelStaleWaste)
├── Exceptions/Domain/      # Custom domain exceptions
├── Http/
│   ├── Controllers/Api/    # API controllers
│   ├── Requests/          # FormRequest validation
│   ├── Resources/         # API Resource transformers
│   └── Support/            # ApiResponse helper
├── Models/                 # Eloquent models (including Waste/* subclasses)
├── Repositories/           # Repository interfaces + implementations
├── Services/               # Business logic layer
└── Providers/              # Service providers
```
