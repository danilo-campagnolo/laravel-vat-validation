# Laravel VAT Validation

A Laravel package for European VAT number validation using the VIES (VAT Information Exchange System) service.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/danilopietrocampagnolo/laravel-vat-validation.svg?style=flat-square)](https://packagist.org/packages/danilopietrocampagnolo/laravel-vat-validation)
[![Total Downloads](https://img.shields.io/packagist/dt/danilopietrocampagnolo/laravel-vat-validation.svg?style=flat-square)](https://packagist.org/packages/danilopietrocampagnolo/laravel-vat-validation)
[![License](https://img.shields.io/packagist/l/danilopietrocampagnolo/laravel-vat-validation.svg?style=flat-square)](https://packagist.org/packages/danilopietrocampagnolo/laravel-vat-validation)

## Features

- 🇪🇺 Validates European VAT numbers using the official VIES service
- 💾 Caching support to reduce API calls
- 🔧 Laravel validation rule integration
- 🎨 Clean and simple API
- 📝 Detailed validation results
- 🚀 Laravel 9.x, 10.x, and 11.x support

## Installation

You can install the package via composer:

```bash
composer require danilopietrocampagnolo/laravel-vat-validation
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=vat-validation-config
```

This will create a `config/vat-validation.php` file where you can customize:

- Cache settings (enable/disable, TTL)
- VIES service URL and timeout
- Exception handling behavior

## Usage

### Using the Facade

```php
use Danilopietrocampagnolo\LaravelVatValidation\Facades\VatValidation;

// Simple validation
$isValid = VatValidation::isValid('NL123456789B01');

// With explicit country code
$isValid = VatValidation::isValid('123456789B01', 'NL');

// Get detailed information
$details = VatValidation::getValidationDetails('NL123456789B01');
/*
Returns:
[
    'valid' => true,
    'original_vat' => 'NL123456789B01',
    'normalized_vat' => 'NL123456789B01',
    'country_code' => 'NL',
    'number_part' => '123456789B01',
    'service_available' => true,
    'error' => null,
    'cached' => false
]
*/

// Skip cache for fresh validation
$isValid = VatValidation::isValidWithoutCache('NL123456789B01');
```

### Using Dependency Injection

```php
use Danilopietrocampagnolo\LaravelVatValidation\Contracts\VatValidationInterface;

class YourController extends Controller
{
    public function __construct(
        private VatValidationInterface $vatValidator
    ) {}

    public function validateVat(Request $request)
    {
        $isValid = $this->vatValidator->isValid($request->vat_number);
        // ...
    }
}
```

### Laravel Validation Rule

The package automatically registers a `vat` validation rule:

```php
// In a form request or controller
$request->validate([
    'vat_number' => 'required|vat',
    // Or with country code parameter
    'vat_number' => 'required|vat:NL',
]);

// Custom error message
$request->validate([
    'vat_number' => 'required|vat'
], [
    'vat_number.vat' => 'Please provide a valid EU VAT number.'
]);
```

### In Blade Templates

```blade
@if(VatValidation::isValid($company->vat_number))
    <span class="text-green-600">✓ Valid VAT</span>
@else
    <span class="text-red-600">✗ Invalid VAT</span>
@endif
```
## Supported Countries

The package supports all EU member states' VAT numbers:

- Austria (AT)
- Belgium (BE)
- Bulgaria (BG)
- Croatia (HR)
- Cyprus (CY)
- Czech Republic (CZ)
- Denmark (DK)
- Estonia (EE)
- Finland (FI)
- France (FR)
- Germany (DE)
- Greece (EL)
- Hungary (HU)
- Ireland (IE)
- Italy (IT)
- Latvia (LV)
- Lithuania (LT)
- Luxembourg (LU)
- Malta (MT)
- Netherlands (NL)
- Poland (PL)
- Portugal (PT)
- Romania (RO)
- Slovakia (SK)
- Slovenia (SI)
- Spain (ES)
- Sweden (SE)

## Error Handling

By default, the package returns `false` for invalid VAT numbers and when the VIES service is unavailable.

To enable exception throwing:

```php
// In config/vat-validation.php
'throw_exceptions' => true,

// Or at runtime
config(['vat-validation.throw_exceptions' => true]);
```

Exception types:

- `VatValidationException`: General validation errors
- `ViesServiceException`: VIES service communication errors

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email danilocampagnolo.dev@gmail.com instead of using the issue tracker.

## Credits

- [Danilo Pietro Campagnolo](https://github.com/danilo-campagnolo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.