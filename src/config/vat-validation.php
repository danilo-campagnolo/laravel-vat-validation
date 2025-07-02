<?php

return [
  /*
  |--------------------------------------------------------------------------
  | Cache Configuration
  |--------------------------------------------------------------------------
  |
  | Enable or disable caching of VAT validation results.
  | Cache TTL is in seconds (default: 24 hours).
  |
  */
  'cache_enabled' => env('VAT_VALIDATION_CACHE_ENABLED', true),
  'cache_ttl' => env('VAT_VALIDATION_CACHE_TTL', 86400),

  /*
  |--------------------------------------------------------------------------
  | VIES Service Configuration
  |--------------------------------------------------------------------------
  |
  | Configuration for the EU VIES service.
  |
  */
  'vies_url' => env('VIES_URL', 'https://ec.europa.eu/taxation_customs/vies/rest-api/ms/'),
  'timeout' => env('VAT_VALIDATION_TIMEOUT', 10),

  /*
  |--------------------------------------------------------------------------
  | Error Handling
  |--------------------------------------------------------------------------
  |
  | Whether to throw exceptions on validation errors or return false.
  |
  */
  'throw_exceptions' => env('VAT_VALIDATION_THROW_EXCEPTIONS', false),
];