<?php

namespace Drupal\commerce_invoice;

/**
 * Represents an invoice number sequence.
 */
final class InvoiceNumberSequence {

  /**
   * The invoice number sequence store ID.
   *
   * @var int
   */
  protected $storeId;

  /**
   * The invoice type.
   *
   * @var string
   */
  protected $invoiceType;

  /**
   * The invoice number sequence.
   *
   * @var int
   */
  protected $sequence;

  /**
   * The timestamp the invoice number sequence was generated.
   *
   * @var int
   */
  protected $generated;

  /**
   * Constructs a new InvoiceNumberSequence object.
   *
   * @param array $definition
   *   The definition.
   */
  public function __construct(array $definition) {
    foreach (['store_id', 'invoice_type', 'sequence', 'generated'] as $required_property) {
      if (empty($definition[$required_property])) {
        throw new \InvalidArgumentException(sprintf('Missing required property %s.', $required_property));
      }
    }
    $this->storeId = $definition['store_id'];
    $this->invoiceType = $definition['invoice_type'];
    $this->sequence = $definition['sequence'];
    $this->generated = $definition['generated'];
  }

  /**
   * Gets the store ID.
   *
   * @return int
   *   The store ID.
   */
  public function getStoreId() {
    return $this->storeId;
  }

  /**
   * Gets the invoice type.
   *
   * @return string
   *   The invoice type.
   */
  public function getInvoiceType() {
    return $this->invoiceType;
  }

  /**
   * Gets the sequence.
   *
   * @return int
   *   The sequence.
   */
  public function getSequence() {
    return $this->sequence;
  }

  /**
   * Gets the generated timestamp.
   *
   * @return int
   *   The generated timestamp.
   */
  public function getGeneratedTime() {
    return $this->generated;
  }

}
