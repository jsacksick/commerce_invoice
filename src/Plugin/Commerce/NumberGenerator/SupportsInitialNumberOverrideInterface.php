<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

/**
 * Defines the interface for number generators which support overriding the initial invoice number.
 */
interface SupportsInitialNumberOverrideInterface {

  /**
   * Gets the initial invoice number.
   *
   * @return int
   *   The initial invoice number.
   */
  public function getInitialNumber();

}
