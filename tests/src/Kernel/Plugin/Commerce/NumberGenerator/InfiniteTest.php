<?php

namespace Drupal\Tests\commerce_invoice\Kernel\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\Entity\Invoice;
use Drupal\commerce_invoice\InvoiceNumberSequence;

/**
 * Tests the infinite invoice number generator.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\Infinite
 * @group commerce_invoice
 */
class InfiniteTest extends NumberGeneratorTestBase {

  /**
   * @covers ::shouldReset
   */
  public function testReset() {
    /** @var \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator */
    $number_generator = $this->numberGeneratorManager->createInstance('infinite');
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
    $this->assertFalse($number_generator->shouldReset($last_sequence));
  }

  /**
   * @covers ::generate
   */
  public function testGenerate() {
    /** @var \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator */
    $number_generator = $this->numberGeneratorManager->createInstance('monthly');
    $number_generator->setConfiguration([
      'padding' => 0,
      'pattern' => 'INV-{number}'
    ]);
    $sequence = new InvoiceNumberSequence([
      'store_id' => $this->store->id(),
      'generated' => strtotime('today'),
      'invoice_type' => 'default',
      'sequence' => 1000,
    ]);
    $invoice = Invoice::create([
      'type' => 'default',
      'store_id' => $this->store->id(),
    ]);
    $this->assertEquals('INV-1000', $number_generator->generate($invoice, $sequence));
  }

}
