<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\InvoiceNumberSequence;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a monthly number generator.
 *
 * @CommerceNumberGenerator(
 *   id = "monthly",
 *   label = @Translation("Monthly (Reset every month, with an id incremented at each invoice generation)"),
 * )
 */
class Monthly extends NumberGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'pattern' => '[current-date:custom:Y-m]-{number}',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function shouldReset(InvoiceNumberSequence $last_sequence) {
    $current_time = DrupalDateTime::createFromTimestamp($this->time->getCurrentTime());
    $generated_time = DrupalDateTime::createFromTimestamp($last_sequence->getGeneratedTime());

    // The invoice number sequence should be reset if the last sequential number
    // was not generated within the same month.
    if (($generated_time->format('Y') != $current_time->format('Y')) ||
      ($generated_time->format('m') != $current_time->format('m'))) {
      return TRUE;
    }

    return FALSE;
  }

}
