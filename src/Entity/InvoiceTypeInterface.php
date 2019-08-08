<?php

namespace Drupal\commerce_invoice\Entity;

use Drupal\commerce\Entity\CommerceBundleEntityInterface;

/**
 * Defines the interface for invoice types.
 */
interface InvoiceTypeInterface extends CommerceBundleEntityInterface {

  /**
   * Gets the invoice type's number pattern.
   **
   * @return \Drupal\commerce_number_pattern\Entity\NumberPatternInterface
   *   The invoice type number pattern.
   */
  public function getNumberPattern();

  /**
   * Gets the invoice type's number pattern ID.
   **
   * @return string
   *   The invoice type number pattern ID.
   */
  public function getNumberPatternId();

  /**
   * Sets the number pattern ID of the invoice type.
   *
   * @param string $number_pattern
   *   The number pattern.
   *
   * @return $this
   */
  public function setNumberPatternId($number_pattern);

  /**
   * Gets the logo file entity.
   *
   * @return \Drupal\file\FileInterface|null
   *   The logo file entity or NULL if it does not exist.
   */
  public function getLogoFile();

  /**
   * Gets the logo URL.
   *
   * @return string|null
   *   The logo URL or NULL if it does not exist.
   */
  public function getLogoUrl();

  /**
   * Sets the logo.
   *
   * @param string $uuid
   *   The UUID of the logo file.
   *
   * @return $this
   */
  public function setLogo($uuid);

  /**
   * Gets the invoice type's footer text.
   **
   * @return string
   *   The invoice type footer text.
   */
  public function getFooterText();

  /**
   * Sets the payment terms of the invoice type.
   *
   * @param string $footer_text
   *   The footer text.
   *
   * @return $this
   */
  public function setFooterText($footer_text);

  /**
   * Gets the invoice type's payment terms.
   **
   * @return string
   *   The invoice type payment terms.
   */
  public function getPaymentTerms();

  /**
   * Sets the payment terms of the invoice type.
   *
   * @param string $payment_terms
   *   The payment terms.
   *
   * @return $this
   */
  public function setPaymentTerms($payment_terms);

  /**
   * Gets the invoice type's workflow ID.
   *
   * Used by the $invoice->state field.
   *
   * @return string
   *   The invoice type workflow ID.
   */
  public function getWorkflowId();

  /**
   * Sets the workflow ID of the invoice type.
   *
   * @param string $workflow_id
   *   The workflow ID.
   *
   * @return $this
   */
  public function setWorkflowId($workflow_id);

}
