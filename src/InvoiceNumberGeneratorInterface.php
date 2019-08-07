<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\Entity\InvoiceTypeInterface;
use Drupal\commerce_store\Entity\StoreInterface;

interface InvoiceNumberGeneratorInterface {

  /**
   * Generate an invoice number for the given invoice.
   *
   * @param InvoiceInterface $invoice
   *   The invoice to generate a number for.
   *
   * @return string|null
   *   The generated invoice number, or NULL if it could not be generated.
   */
  public function generateInvoiceNumber(InvoiceInterface $invoice);

}
