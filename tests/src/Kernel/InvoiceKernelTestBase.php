<?php

namespace Drupal\Tests\commerce_invoice\Kernel;

use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Provides a base class for invoice kernel tests.
 */
abstract class InvoiceKernelTestBase extends CommerceKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'profile',
    'state_machine',
    'commerce_order',
    'commerce_invoice',
    'commerce_invoice_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_order_item');
    $this->installEntitySchema('commerce_invoice');
    $this->installEntitySchema('commerce_invoice_item');
    $this->installConfig('commerce_invoice');
    $this->installSchema('commerce_invoice', ['commerce_invoice_number_sequence']);
  }

}
