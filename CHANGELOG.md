# Changelog

All notable changes to `laravel-vat-validation` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.6.0] - 2025-07-02

### Added
- Laravel 12.x support
- New Artisan command `vat:validate` for validating VAT numbers from the command line and return vat details

### Changed
- Updated Laravel version compatibility to include 12.x

## [0.5.0] - 2025-07-02

### Added
- Initial release of Laravel VAT Validation package
- European VAT number validation using VIES (VAT Information Exchange System) service
- Support for all 27 EU member states VAT numbers
- Caching support to reduce API calls and improve performance
- Laravel validation rule integration (`vat` rule)
- Facade support for easy access (`VatValidation` facade)
- Dependency injection support via `VatValidationInterface`
- Detailed validation results with comprehensive information
- Configurable exception handling (silent mode or throw exceptions)
- Configuration file for customizing cache settings, VIES service URL, and timeout
- Support for Laravel 9.x, 10.x, and 11.x
- Comprehensive PHPUnit test suite
- Clean and intuitive API design

### Features
- `isValid()` method for simple boolean validation
- `getValidationDetails()` method for detailed validation information
- `isValidWithoutCache()` method to bypass cache for fresh validation
- Automatic service provider registration
- Blade template helper support
- Custom error messages support
- Graceful fallback when VIES service is unavailable

### Security
- Input sanitization and validation
- Safe handling of external API responses
- Proper error handling to prevent information leakage

### Documentation
- Comprehensive README with usage examples
- Configuration documentation
- API reference and method descriptions
- Installation and setup instructions

[0.6.0]: https://github.com/danilo-campagnolo/laravel-vat-validation/releases/tag/v0.6.0
[0.5.0]: https://github.com/danilo-campagnolo/laravel-vat-validation/releases/tag/v0.5.0