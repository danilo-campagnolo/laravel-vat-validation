<?php

namespace Danilopietrocampagnolo\LaravelVatValidation;

use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Psr\Log\LoggerInterface;
use Danilopietrocampagnolo\LaravelVatValidation\Contracts\VatValidationInterface;
use Danilopietrocampagnolo\LaravelVatValidation\Exceptions\VatValidationException;
use Danilopietrocampagnolo\LaravelVatValidation\Exceptions\ViesServiceException;

class VatValidationService implements VatValidationInterface
{
  protected CacheRepository $cache;
  protected LoggerInterface $logger;
  protected array $config;

  public function __construct(
    CacheRepository $cache,
    LoggerInterface $logger,
    array $config = []
  ) {
    $this->cache = $cache;
    $this->logger = $logger;
    $this->config = array_merge($this->getDefaultConfig(), $config);
  }

  /**
   * Get default configuration
   */
  protected function getDefaultConfig(): array
  {
    return [
      'cache_enabled' => true,
      'cache_ttl' => 86400, // 24 hours
      'timeout' => 10,
      'vies_url' => 'https://ec.europa.eu/taxation_customs/vies/rest-api/ms/',
      'throw_exceptions' => false,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isValid(string $vatNumber, ?string $countryCode = null): bool
  {
    try {
      if (empty($vatNumber)) {
        return false;
      }

      $normalizedVat = $this->normalizeVatNumber($vatNumber);
      $vatWithCountry = $this->ensureCountryCodePrefix($normalizedVat, $countryCode);
      $extractedCountryCode = $this->extractCountryCode($vatWithCountry);

      if (!$extractedCountryCode) {
        return false;
      }

      if ($this->config['cache_enabled']) {
        return $this->validateWithCache($vatWithCountry, $extractedCountryCode);
      }

      return $this->validateWithVIES($vatWithCountry, $extractedCountryCode) ?? false;
    } catch (\Exception $e) {
      if ($this->config['throw_exceptions']) {
        throw new VatValidationException($e->getMessage(), 0, $e);
      }
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isValidWithoutCache(string $vatNumber, ?string $countryCode = null): bool
  {
    $previousCacheEnabled = $this->config['cache_enabled'];
    $this->config['cache_enabled'] = false;

    try {
      return $this->isValid($vatNumber, $countryCode);
    } finally {
      $this->config['cache_enabled'] = $previousCacheEnabled;
    }
  }

  /**
   * Validate with cache
   */
  protected function validateWithCache(string $vatNumber, string $countryCode): bool
  {
    $cacheKey = $this->getCacheKey($vatNumber, $countryCode);

    return $this->cache->remember(
      $cacheKey,
      $this->config['cache_ttl'],
      fn() => $this->validateWithVIES($vatNumber, $countryCode) ?? false
    );
  }

  /**
   * Generate cache key
   */
  protected function getCacheKey(string $vatNumber, string $countryCode): string
  {
    return "vat_validation:{$countryCode}:{$vatNumber}";
  }

  /**
   * Normalize VAT number
   */
  protected function normalizeVatNumber(string $vatNumber): string
  {
    return strtoupper(preg_replace('/[\s\-\.]/', '', $vatNumber));
  }

  /**
   * Ensure VAT number has country code prefix
   */
  protected function ensureCountryCodePrefix(string $vatNumber, ?string $countryCode = null): string
  {
    if (preg_match('/^[A-Z]{2}/', $vatNumber)) {
      return $vatNumber;
    }

    if ($countryCode && preg_match('/^[A-Z]{2}$/', strtoupper($countryCode))) {
      return strtoupper($countryCode) . $vatNumber;
    }

    return $vatNumber;
  }

  /**
   * Extract country code from VAT number
   */
  protected function extractCountryCode(string $vatNumber): ?string
  {
    $prefix = substr($vatNumber, 0, 2);
    if (preg_match('/^[A-Z]{2}$/', $prefix)) {
      // Handle special case for Greece
      return $prefix === 'GR' ? 'EL' : $prefix;
    }

    return null;
  }

  /**
   * Extract VAT number part (without country code)
   */
  protected function extractVatNumberPart(string $vatNumber, string $countryCode): string
  {
    if (strpos($vatNumber, $countryCode) === 0) {
      return substr($vatNumber, strlen($countryCode));
    }

    if (preg_match('/^[A-Z]{2}(.+)$/', $vatNumber, $matches)) {
      return $matches[1];
    }

    return $vatNumber;
  }

  /**
   * Validate using VIES service
   */
  protected function validateWithVIES(string $vatNumber, string $countryCode): ?bool
  {
    try {
      $numberPart = $this->extractVatNumberPart($vatNumber, $countryCode);
      $url = $this->config['vies_url'] . $countryCode . '/vat/' . $numberPart;

      $response = Http::timeout($this->config['timeout'])->get($url);

      if ($response->successful()) {
        $data = $response->json();
        return $data['isValid'] ?? false;
      }

      $this->logger->warning('VIES VAT validation failed - HTTP error', [
        'vat_number' => $vatNumber,
        'country_code' => $countryCode,
        'status' => $response->status(),
        'body' => $response->body()
      ]);

      if ($this->config['throw_exceptions']) {
        throw new ViesServiceException(
          "VIES service returned status {$response->status()}",
          $response->status()
        );
      }

    } catch (\Exception $e) {
      $this->logger->warning('VIES VAT validation failed - Exception', [
        'vat_number' => $vatNumber,
        'country_code' => $countryCode,
        'error' => $e->getMessage()
      ]);

      if ($this->config['throw_exceptions'] && !($e instanceof ViesServiceException)) {
        throw new ViesServiceException($e->getMessage(), 0, $e);
      }
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getValidationDetails(string $vatNumber, ?string $countryCode = null): array
  {
    if (empty($vatNumber)) {
      return [
        'valid' => false,
        'error' => 'VAT number is empty',
        'original_vat' => $vatNumber,
        'normalized_vat' => null,
        'country_code' => null
      ];
    }

    $normalizedVat = $this->normalizeVatNumber($vatNumber);
    $vatWithCountry = $this->ensureCountryCodePrefix($normalizedVat, $countryCode);
    $extractedCountryCode = $this->extractCountryCode($vatWithCountry);

    if (!$extractedCountryCode) {
      return [
        'valid' => false,
        'error' => 'Could not determine country code',
        'original_vat' => $vatNumber,
        'normalized_vat' => $normalizedVat,
        'country_code' => null
      ];
    }

    $viesResult = $this->validateWithVIES($vatWithCountry, $extractedCountryCode);

    return [
      'valid' => $viesResult ?? false,
      'original_vat' => $vatNumber,
      'normalized_vat' => $vatWithCountry,
      'country_code' => $extractedCountryCode,
      'number_part' => $this->extractVatNumberPart($vatWithCountry, $extractedCountryCode),
      'service_available' => $viesResult !== null,
      'error' => $viesResult === null ? 'VIES service unavailable' : null,
      'cached' => $this->config['cache_enabled'] && $this->cache->has($this->getCacheKey($vatWithCountry, $extractedCountryCode))
    ];
  }
}