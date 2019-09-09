<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;

/**
 * Handles generating PDFS for invoices.
 */
interface InvoicePrintBuilderInterface {

  /**
   * Builds a PDF for the given invoice.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice
   *   The invoice.
   *
   * @return \Drupal\file\FileInterface|null
   *   The invoice PDF file, FALSE it could not be created.
   */
  public function build(InvoiceInterface $invoice);

}
