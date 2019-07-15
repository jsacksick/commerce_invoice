<?php

namespace Drupal\commerce_invoice\Entity;

use Drupal\commerce\Entity\CommerceBundleEntityInterface;

/**
 * Defines the interface for invoice types.
 */
interface InvoiceTypeInterface extends CommerceBundleEntityInterface {

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
