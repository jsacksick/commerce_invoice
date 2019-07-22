<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;

interface InvoiceNumberGeneratorInterface {

  /**
   * Generate an invoice number for the given invoice.
   *
   * @param InvoiceInterface $invoice
   *   The invoice to generate a number for.
   *
   * @return string
   *   The generated invoice number.
   */
  public function generateInvoiceNumber(InvoiceInterface $invoice);

}
