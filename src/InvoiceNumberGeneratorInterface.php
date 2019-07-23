<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface;
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
   * @param \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator
   *   The number generator plugin.
   * @param \Drupal\commerce_invoice\InvoiceNumberSequence|null $current_sequence
   *   The current invoice number sequence, or NULL if the sequence hasn't
   *   started yet.
   * @param bool $update
   *   (optional) Whether to insert/update the sequence in DB (or simply get
   *   the next invoice number sequence). Defaults to TRUE.
   *
   * @return \Drupal\commerce_invoice\InvoiceNumberSequence
   *   The next invoice number sequence.
   */
  public function getNextSequence(StoreInterface $store, NumberGeneratorInterface $number_generator, $update = TRUE);

}
