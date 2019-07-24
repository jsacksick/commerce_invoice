<?php

namespace Drupal\Tests\commerce_invoice\Kernel;

use Drupal\commerce_invoice\Entity\Invoice;
use Drupal\commerce_invoice\Entity\InvoiceType;

/**
 * Tests the invoice number generator service.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\InvoiceNumberGenerator
 * @group commerce_invoice
 */
class InvoiceNumberGenerator extends InvoiceKernelTestBase {

  /**
   * The invoice number generator service.
   *
   * @var \Drupal\commerce_invoice\InvoiceNumberGeneratorInterface
   */
  protected $invoiceNumberGenerator;

  /**
   * The second store.
   *
   * @var \Drupal\commerce_store\Entity\StoreInterface
   */
  protected $store2;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['token'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->store2 = $this->createStore('Second store', 'admin2@example.com', 'online', FALSE);
    $this->invoiceNumberGenerator = $this->container->get('commerce_invoice.invoice_number_generator');
  }

  /**
   * Tests generating invoice numbers for invoices using the infinite plugin.
   *
   * @covers ::generateInvoiceNumber
   * @covers ::getNextSequence
   * @covers ::resetSequence
   */
  public function testInfinite() {
    // Skip saving the invoice to make sure no invoice number is generated.
    $invoice = $this->createInvoice($this->store, 'default', [], TRUE);
    $another_invoice = $this->createInvoice($this->store2, 'default', [], TRUE);
    // Assert that the initial invoice number is 1, when not overridden.
    $this->assertEquals(1, $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
    $this->assertEquals(2, $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
    $this->assertEquals(1, $this->invoiceNumberGenerator->generateInvoiceNumber($another_invoice));

    $invoice_type = InvoiceType::load('default');
    $configuration = $invoice_type->getNumberGeneratorConfiguration();
    $configuration['initialNumber'] = 2000;
    $invoice_type->setNumberGeneratorConfiguration($configuration);
    $invoice_type->save();
    $this->invoiceNumberGenerator->resetSequence($this->store, $invoice_type);
    $this->assertEquals(2000, $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
    $this->assertEquals(2001, $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
  }

  /**
   * Tests generating invoice numbers for invoices using the monthly plugin.
   *
   * @covers ::generateInvoiceNumber
   * @covers ::getNextSequence
   */
  public function testMonthly() {
    $test_invoice_type = $this->createInvoiceType(NULL, NULL, [
      'numberGenerator' => 'monthly',
    ]);
    $invoice = $this->createInvoice($this->store, $test_invoice_type->id(), [], TRUE);
    $prefix = date('Y-m');
    $this->assertEquals($prefix . '-1', $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
    $this->assertEquals($prefix . '-2', $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
    $invoice->setStore($this->store2);
    $this->assertEquals($prefix . '-1', $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
  }

  /**
   * Tests generating invoice numbers for invoices using the monthly plugin.
   *
   * @covers ::generateInvoiceNumber
   * @covers ::getNextSequence
   */
  public function testYearly() {
    $test_invoice_type = $this->createInvoiceType(NULL, NULL, [
      'numberGenerator' => 'yearly',
    ]);
    $invoice = $this->createInvoice($this->store, $test_invoice_type->id(), [], TRUE);
    $prefix = date('Y');
    $this->assertEquals($prefix . '-1', $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
    $this->assertEquals($prefix . '-2', $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
    $invoice->setStore($this->store2);
    $this->assertEquals($prefix . '-1', $this->invoiceNumberGenerator->generateInvoiceNumber($invoice));
  }

}
