<?php

namespace Drupal\Tests\commerce_invoice\Kernel\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\Entity\Invoice;
use Drupal\commerce_invoice\InvoiceNumberSequence;

/**
 * Tests the monthly invoice number generator.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\Monthly
 * @group commerce_invoice
 */
class MonthlyTest extends NumberGeneratorTestBase {

  /**
   * @covers ::shouldReset
   */
  public function testReset() {
    /** @var \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator */
    $number_generator = $this->numberGeneratorManager->createInstance('monthly');
    $definition = [
      'store_id' => $this->store->id(),
      'generated' => strtotime('today'),
      'invoice_type' => 'default',
      'sequence' => 10,
    ];
    $last_sequence = new InvoiceNumberSequence($definition);
    $this->assertFalse($number_generator->shouldReset($last_sequence));
    $definition['generated'] = strtotime('-35 days');
    $last_sequence = new InvoiceNumberSequence($definition);
    $this->assertTrue($number_generator->shouldReset($last_sequence));
  }

  /**
   * @covers ::generate
   */
  public function testGenerate() {
    /** @var \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator */
    $number_generator = $this->numberGeneratorManager->createInstance('monthly');
    $sequence = new InvoiceNumberSequence([
      'store_id' => $this->store->id(),
      'generated' => strtotime('today'),
      'invoice_type' => 'default',
      'sequence' => 10,
    ]);
    $invoice = Invoice::create([
      'type' => 'default',
      'store_id' => $this->store->id(),
    ]);
    $current_month = date('Y-m');
    $this->assertEquals($current_month . '-10', $number_generator->generate($invoice, $sequence));
  }

}