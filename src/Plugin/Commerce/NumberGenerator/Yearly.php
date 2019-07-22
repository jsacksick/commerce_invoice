<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\InvoiceNumberSequence;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a yearly number generator.
 *
 * @CommerceNumberGenerator(
 *   id = "yearly",
 *   label = @Translation("Yearly"),
 * )
 */
class Yearly extends NumberGeneratorBase {

  /**
   * {@inheritdoc}
   */
  public function shouldReset(InvoiceNumberSequence $last_sequence) {
    $current_time = DrupalDateTime::createFromTimestamp($this->time->getCurrentTime());
    $generated_time = DrupalDateTime::createFromTimestamp($last_sequence->getGeneratedTime());
    // The sequence should be reset if the current year doesn't match the year
    // the last sequential number was generated.
    return $generated_time->format('Y') != $current_time->format('Y');
  }

}
