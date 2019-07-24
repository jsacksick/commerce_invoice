<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\InvoiceNumberSequence;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Provides an interface for number generator plugins.
 */
interface NumberGeneratorInterface extends ConfigurableInterface, DependentPluginInterface, PluginInspectionInterface, PluginFormInterface {

  /**
   * Gets the invoice number pattern.
   *
   * @return string
   *   The invoice number pattern.
   */
  public function getPattern();

  /**
   * Gets the invoice number padding.
   *
   * @return int
   *   The invoice number padding.
   */
  public function getPadding();

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

}
