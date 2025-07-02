<?php

namespace Danilopietrocampagnolo\LaravelVatValidation;

use Danilopietrocampagnolo\LaravelVatValidation\Console\Commands\ValidateVatCommand;
use Danilopietrocampagnolo\LaravelVatValidation\Contracts\VatValidationInterface;
use Illuminate\Support\ServiceProvider;

class VatValidationServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    $this->mergeConfigFrom(
      __DIR__ . '/config/vat-validation.php',
      'vat-validation'
    );

    // Register as singleton with the key 'vat-validation'
    $this->app->singleton('vat-validation', function ($app) {
      return new VatValidationService(
        $app['cache.store'],
        $app['log'],
        $app['config']->get('vat-validation')
      );
    });

    // Also bind the interface
    $this->app->bind(VatValidationInterface::class, function ($app) {
      return $app->make('vat-validation');
    });
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__ . '/config/vat-validation.php' => config_path('vat-validation.php'),
      ], 'vat-validation-config');

      $this->commands([
        ValidateVatCommand::class,
      ]);
    }

    $this->registerValidationRules();
  }

  /**
   * Register custom validation rules
   */
  protected function registerValidationRules(): void
  {
    $this->app['validator']->extend('vat', function ($attribute, $value, $parameters, $validator) {
      $countryCode = $parameters[0] ?? null;
      return app(VatValidationInterface::class)->isValid($value, $countryCode);
    });

    $this->app['validator']->replacer('vat', function ($message, $attribute, $rule, $parameters) {
      return str_replace(':attribute', $attribute, 'The :attribute must be a valid VAT number.');
    });
  }
}