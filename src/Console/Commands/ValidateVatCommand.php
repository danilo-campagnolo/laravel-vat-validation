<?php

namespace Danilopietrocampagnolo\LaravelVatValidation\Console\Commands;

use Illuminate\Console\Command;
use Danilopietrocampagnolo\LaravelVatValidation\Contracts\VatValidationInterface;

class ValidateVatCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'vat:validate 
                            {vat : The VAT number to validate}
                            {--country= : Optional country code (e.g., NL, DE, IT)}
                            {--no-cache : Skip cache and force fresh validation}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Validate a European VAT number using VIES service';

  /**
   * Execute the console command.
   */
  public function handle(VatValidationInterface $vatValidator): int
  {
    $vatNumber = $this->argument('vat');
    $countryCode = $this->option('country');
    $noCache = $this->option('no-cache');

    $this->info('Validating VAT number...');
    $this->newLine();

    try {
      // Get validation details
      $details = $vatValidator->getValidationDetails($vatNumber, $countryCode);

      // Display results
      $this->displayResults($details);

      // If using cache and user wants fresh validation
      if ($noCache && $details['cached'] ?? false) {
        $this->newLine();
        $this->info('Running fresh validation without cache...');
        $isValid = $vatValidator->isValidWithoutCache($vatNumber, $countryCode);
        $details = $vatValidator->getValidationDetails($vatNumber, $countryCode);
        $this->displayResults($details);
      }

      return $details['valid'] ? Command::SUCCESS : Command::FAILURE;

    } catch (\Exception $e) {
      $this->error('Error validating VAT number: ' . $e->getMessage());
      return Command::FAILURE;
    }
  }

  /**
   * Display validation results
   */
  protected function displayResults(array $details): void
  {
    // Status header
    if ($details['valid']) {
      $this->components->info('✓ VAT number is VALID');
    } else {
      $this->components->error('✗ VAT number is INVALID');
    }

    $this->newLine();

    // Details table
    $this->table(
      ['Field', 'Value'],
      [
        ['Original VAT', $details['original_vat'] ?? 'N/A'],
        ['Normalized VAT', $details['normalized_vat'] ?? 'N/A'],
        ['Country Code', $details['country_code'] ?? 'N/A'],
        ['Number Part', $details['number_part'] ?? 'N/A'],
        ['Valid', $details['valid'] ? '✓ Yes' : '✗ No'],
        ['Service Available', $details['service_available'] ? '✓ Yes' : '✗ No'],
        ['From Cache', ($details['cached'] ?? false) ? '✓ Yes' : '✗ No'],
        ['Error', $details['error'] ?? 'None'],
      ]
    );

    // Additional info
    if (!$details['service_available']) {
      $this->newLine();
      $this->warn('⚠️  VIES service is currently unavailable. The result may not be accurate.');
    }

    if ($details['cached'] ?? false) {
      $this->newLine();
      $this->info('ℹ️  This result was retrieved from cache. Use --no-cache for fresh validation.');
    }
  }
}