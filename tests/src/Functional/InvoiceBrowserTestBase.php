<?php

namespace Drupal\Tests\commerce_invoice\Functional;

use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Defines base class for commerce_invoice test cases.
 */
abstract class InvoiceBrowserTestBase extends CommerceBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_invoice',
    'commerce_invoice_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_invoice',
      'administer commerce_invoice_type',
      'access commerce_invoice overview',
    ], parent::getAdministratorPermissions());
  }

}
