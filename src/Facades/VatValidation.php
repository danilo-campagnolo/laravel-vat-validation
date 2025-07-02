<?php

namespace Danilopietrocampagnolo\LaravelVatValidation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isValid(string $vatNumber, ?string $countryCode = null)
 * @method static array getValidationDetails(string $vatNumber, ?string $countryCode = null)
 * @method static bool isValidWithoutCache(string $vatNumber, ?string $countryCode = null)
 *
 * @see \Danilopietrocampagnolo\LaravelVatValidation\VatValidationService
 */
class VatValidation extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'vat-validation';
  }
}