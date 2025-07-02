<?php

namespace LaravelVatValidation\VatValidation\Facades;

use Danilopietrocampagnolo\LaravelVatValidation\Contracts\VatValidationInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isValid(string $vatNumber, ?string $countryCode = null)
 * @method static array getValidationDetails(string $vatNumber, ?string $countryCode = null)
 * @method static bool isValidWithoutCache(string $vatNumber, ?string $countryCode = null)
 *
 * @see \LaravelVatValidation\VatValidation\VatValidationService
 */
class VatValidation extends Facade
{
  protected static function getFacadeAccessor()
  {
    return 'vat-validation';
  }
}