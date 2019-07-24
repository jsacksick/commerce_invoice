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
   * @return string
   *   The generated invoice number.
   */
  public function generateInvoiceNumber(InvoiceInterface $invoice);

  /**
   * Gets the next invoice number sequence.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store.
   * @param \Drupal\commerce_invoice\Entity\InvoiceTypeInterface $invoice_type
   *   The invoice type.
   * @param bool $update
   *   (optional) Whether to insert/update the sequence in DB (or simply get
   *   the next invoice number sequence). Defaults to TRUE.
   *
   * @return \Drupal\commerce_invoice\InvoiceNumberSequence
   *   The next invoice number sequence.
   */
  public function getNextSequence(StoreInterface $store, InvoiceTypeInterface $invoice_type, $update = TRUE);

  /**
   * Reset the invoice number sequence for the given store/plugin.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store.
   * @param \Drupal\commerce_invoice\Entity\InvoiceTypeInterface $invoice_type
   *   The invoice type.
   */
  public function resetSequence(StoreInterface $store, InvoiceTypeInterface $invoice_type);

}
