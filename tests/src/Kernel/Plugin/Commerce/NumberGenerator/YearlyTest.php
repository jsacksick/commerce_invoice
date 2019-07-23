<?php

namespace Drupal\Tests\commerce_invoice\Kernel\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\Entity\Invoice;
use Drupal\commerce_invoice\InvoiceNumberSequence;

/**
 * Tests the yearly invoice number generator.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\Yearly
 * @group commerce_invoice
 */
class YearlyTest extends NumberGeneratorTestBase {

  /**
   * @covers ::shouldReset
   */
  public function testReset() {
    /** @var \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator */
    $number_generator = $this->numberGeneratorManager->createInstance('yearly');
    $definition = [
      'store_id' => $this->store->id(),
      'generated' => strtotime('today'),
      'plugin_id' => 'yearly',
      'sequence' => 10,
    ];
    $last_sequence = new InvoiceNumberSequence($definition);
    $this->assertFalse($number_generator->shouldReset($last_sequence));
    $definition['generated'] = strtotime('-35 days');
    $last_sequence = new InvoiceNumberSequence($definition);
    $this->assertFalse($number_generator->shouldReset($last_sequence));
    $definition['generated'] = strtotime('-370 days');
    $last_sequence = new InvoiceNumberSequence($definition);
    $this->assertTrue($number_generator->shouldReset($last_sequence));
  }

  /**
   * @covers ::generate
   */
  public function testGenerate() {
    /** @var \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator */
    $number_generator = $this->numberGeneratorManager->createInstance('yearly');
    $sequence = new InvoiceNumberSequence([
      'store_id' => $this->store->id(),
      'generated' => strtotime('today'),
      'plugin_id' => 'yearly',
      'sequence' => 10,
    ]);
    $invoice = Invoice::create([
      'type' => 'default',
      'store_id' => $this->store->id(),
    ]);
    $current_year = date('Y');
    $this->assertEquals($current_year . '-10', $number_generator->generate($invoice, $sequence));
  }

}
