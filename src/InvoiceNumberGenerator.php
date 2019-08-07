<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\Entity\InvoiceType;

/**
 * Provides a service for generating invoice numbers.
 */
class InvoiceNumberGenerator implements InvoiceNumberGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public function generateInvoiceNumber(InvoiceInterface $invoice) {
    $invoice_type = InvoiceType::load($invoice->bundle());
    /** @var \Drupal\commerce_number_pattern\Entity\NumberPatternInterface $number_pattern */
    $number_pattern = $invoice_type->getNumberPattern();

    if (!$number_pattern) {
      return NULL;
    }

    return $number_pattern->getPlugin()->generate($invoice);
  }

}
