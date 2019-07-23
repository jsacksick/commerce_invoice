<?php

namespace Drupal\Tests\commerce_invoice\Kernel\Plugin\Commerce\NumberGenerator;

use Drupal\Tests\commerce_invoice\Kernel\InvoiceKernelTestBase;

/**
 * Provides a base test class for number generator plugins.
 */
abstract class NumberGeneratorTestBase extends InvoiceKernelTestBase {

  /**
   * The number generator manager.
   *
   * @var \Drupal\commerce_invoice\NumberGeneratorManager
   */
  protected $numberGeneratorManager;

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

    $this->numberGeneratorManager = $this->container->get('plugin.manager.commerce_number_generator');
  }

}
