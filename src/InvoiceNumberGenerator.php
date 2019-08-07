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
    // @todo: Get the number pattern config entity from the invoice type and
    // call the number generator plugin to generate a number.
    return 10;
  }

}
