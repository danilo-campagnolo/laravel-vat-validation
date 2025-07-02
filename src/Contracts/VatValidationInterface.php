<?php

namespace Danilopietrocampagnolo\LaravelVatValidation\Contracts;

interface VatValidationInterface
{
    /**
     * Validate a VAT number
     *
     * @param string $vatNumber
     * @param string|null $countryCode
     * @return bool
     */
    public function isValid(string $vatNumber, ?string $countryCode = null): bool;

    /**
     * Get detailed validation information
     *
     * @param string $vatNumber
     * @param string|null $countryCode
     * @return array
     */
    public function getValidationDetails(string $vatNumber, ?string $countryCode = null): array;

    /**
     * Validate a VAT number without caching
     *
     * @param string $vatNumber
     * @param string|null $countryCode
     * @return bool
     */
    public function isValidWithoutCache(string $vatNumber, ?string $countryCode = null): bool;
}