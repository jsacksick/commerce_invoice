<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\InvoiceNumberSequence;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides an interface for number generator plugins.
 */
interface NumberGeneratorInterface extends PluginInspectionInterface {

  /**
   * Gets whether the invoice number sequence should be reset.
   *
   * @param \Drupal\commerce_invoice\InvoiceNumberSequence $last_sequence
   *   The last invoice number sequence.
   *
   * @return bool
   *   Whether the invoice number sequence should be reset.
   */
  public function shouldReset(InvoiceNumberSequence $last_sequence);

  /**
   * Generate an invoice number for the given invoice, and the given sequence.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice
   *   The invoice to generate a number for.
   * @param \Drupal\commerce_invoice\InvoiceNumberSequence $invoice_number_sequence
   *   The invoice number sequence.
   *
   * @return string
   *   The generated invoice number.
   */
  public function generate(InvoiceInterface $invoice, InvoiceNumberSequence $invoice_number_sequence);

}
