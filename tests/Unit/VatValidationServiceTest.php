<?php

namespace Danilopietrocampagnolo\LaravelVatValidation\Tests\Unit;

use Danilopietrocampagnolo\LaravelVatValidation\Tests\TestCase;
use Danilopietrocampagnolo\LaravelVatValidation\VatValidationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VatValidationServiceTest extends TestCase
{
    protected VatValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new VatValidationService(
            Cache::store(),
            Log::getFacadeRoot(),
            ['cache_enabled' => false]
        );
    }

    public function test_normalizes_vat_numbers()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('normalizeVatNumber');
        $method->setAccessible(true);

        $this->assertEquals('NL123456789B01', $method->invoke($this->service, 'nl 123-456-789.b01'));
        $this->assertEquals('DE123456789', $method->invoke($this->service, 'DE 123 456 789'));
    }

    public function test_extracts_country_code()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractCountryCode');
        $method->setAccessible(true);

        $this->assertEquals('NL', $method->invoke($this->service, 'NL123456789B01'));
        $this->assertEquals('EL', $method->invoke($this->service, 'GR123456789')); // Greece special case
        $this->assertNull($method->invoke($this->service, '123456789'));
    }

    public function test_ensures_country_code_prefix()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('ensureCountryCodePrefix');
        $method->setAccessible(true);

        $this->assertEquals('NL123456789B01', $method->invoke($this->service, 'NL123456789B01', null));
        $this->assertEquals('NL123456789B01', $method->invoke($this->service, '123456789B01', 'NL'));
        $this->assertEquals('123456789', $method->invoke($this->service, '123456789', null));
    }

    public function test_validates_empty_vat_number()
    {
        $this->assertFalse($this->service->isValid(''));
        $this->assertFalse($this->service->isValid('   '));
    }

    public function test_validates_vat_with_vies_success()
    {
        Http::fake([
            'https://ec.europa.eu/taxation_customs/vies/rest-api/ms/NL/vat/123456789B01' => Http::response([
                'isValid' => true,
                'userError' => 'VALID',
            ], 200),
        ]);

        $this->assertTrue($this->service->isValid('NL123456789B01'));
    }

    public function test_validates_vat_with_vies_invalid()
    {
        Http::fake([
            'https://ec.europa.eu/taxation_customs/vies/rest-api/ms/NL/vat/123456789B01' => Http::response([
                'isValid' => false,
                'userError' => 'INVALID_INPUT',
            ], 200),
        ]);

        $this->assertFalse($this->service->isValid('NL123456789B01'));
    }

    public function test_handles_vies_service_error()
    {
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $this->assertFalse($this->service->isValid('NL123456789B01'));
    }

    public function test_caching_works()
    {
        $service = new VatValidationService(
            Cache::store(),
            Log::getFacadeRoot(),
            ['cache_enabled' => true, 'cache_ttl' => 60]
        );

        Http::fake([
            '*' => Http::sequence()
                ->push(['isValid' => true], 200)
                ->push(['isValid' => false], 200),
        ]);

        // First call should hit the API
        $this->assertTrue($service->isValid('NL123456789B01'));

        // Second call should use cache (still true)
        $this->assertTrue($service->isValid('NL123456789B01'));
    }

    public function test_get_validation_details()
    {
        Http::fake([
            '*' => Http::response([
                'isValid' => true,
            ], 200),
        ]);

        $details = $this->service->getValidationDetails('NL123456789B01');

        $this->assertTrue($details['valid']);
        $this->assertEquals('NL123456789B01', $details['original_vat']);
        $this->assertEquals('NL123456789B01', $details['normalized_vat']);
        $this->assertEquals('NL', $details['country_code']);
        $this->assertEquals('123456789B01', $details['number_part']);
        $this->assertTrue($details['service_available']);
        $this->assertNull($details['error']);
    }
}