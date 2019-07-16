<?php

namespace Drupal\commerce_invoice\Entity;

use Drupal\commerce_order\EntityAdjustableInterface;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Defines the interface for invoices.
 */
interface InvoiceInterface extends ContentEntityInterface, EntityAdjustableInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the invoice number.
   *
   * @return string
   *   The invoice number.
   */
  public function getInvoiceNumber();

  /**
   * Sets the invoice number.
   *
   * @param string $invoice_number
   *   The invoice number.
   *
   * @return $this
   */
  public function setInvoiceNumber($invoice_number);

  /**
   * Gets the store.
   *
   * @return \Drupal\commerce_store\Entity\StoreInterface|null
   *   The store entity, or null.
   */
  public function getStore();

  /**
   * Sets the store.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store entity.
   *
   * @return $this
   */
  public function setStore(StoreInterface $store);

  /**
   * Gets the store ID.
   *
   * @return int
   *   The store ID.
   */
  public function getStoreId();

  /**
   * Sets the store ID.
   *
   * @param int $store_id
   *   The store ID.
   *
   * @return $this
   */
  public function setStoreId($store_id);

  /**
   * Gets the customer user.
   *
   * @return \Drupal\user\UserInterface
   *   The customer user entity. If the invoice is anonymous (customer
   *   unspecified or deleted), an anonymous user will be returned. Use
   *   $customer->isAnonymous() to check.
   */
  public function getCustomer();

  /**
   * Sets the customer user.
   *
   * @param \Drupal\user\UserInterface $account
   *   The customer user entity.
   *
   * @return $this
   */
  public function setCustomer(UserInterface $account);

  /**
   * Gets the customer user ID.
   *
   * @return int
   *   The customer user ID ('0' if anonymous).
   */
  public function getCustomerId();

  /**
   * Sets the customer user ID.
   *
   * @param int $uid
   *   The customer user ID.
   *
   * @return $this
   */
  public function setCustomerId($uid);

  /**
   * Gets the billing profile.
   *
   * @return \Drupal\profile\Entity\ProfileInterface|null
   *   The billing profile, or NULL if none found.
   */
  public function getBillingProfile();

  /**
   * Sets the billing profile.
   *
   * @param \Drupal\profile\Entity\ProfileInterface $profile
   *   The billing profile.
   *
   * @return $this
   */
  public function setBillingProfile(ProfileInterface $profile);

  /**
   * Gets the invoice items.
   *
   * @return \Drupal\commerce_invoice\Entity\InvoiceItemInterface[]
   *   The invoice items.
   */
  public function getItems();

  /**
   * Sets the invoice items.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceItemInterface[] $invoice_items
   *   The invoice items.
   *
   * @return $this
   */
  public function setItems(array $invoice_items);

  /**
   * Gets whether the invoice has invoice items.
   *
   * @return bool
   *   TRUE if the invoice has invoice items, FALSE otherwise.
   */
  public function hasItems();

  /**
   * Adds an invoice item.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceItemInterface $invoice_item
   *   The invoice item.
   *
   * @return $this
   */
  public function addItem(InvoiceItemInterface $invoice_item);

  /**
   * Removes an invoice item.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceItemInterface $invoice_item
   *   The invoice item.
   *
   * @return $this
   */
  public function removeItem(InvoiceItemInterface $invoice_item);

  /**
   * Checks whether the invoice has a given invoice item.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceItemInterface $invoice_item
   *   The invoice item.
   *
   * @return bool
   *   TRUE if the invoice item was found, FALSE otherwise.
   */
  public function hasItem(InvoiceItemInterface $invoice_item);

  /**
   * Collects all adjustments that belong to the invoice.
   *
   * Unlike getAdjustments() which returns only invoice adjustments, this
   * method returns both invoice and invoice item adjustments.
   *
   * Important:
   * The returned adjustments are unprocessed, and must be processed before use.
   *
   * @param string[] $adjustment_types
   *   The adjustment types to include.
   *   Examples: fee, promotion, tax. Defaults to all adjustment types.
   *
   * @return \Drupal\commerce_order\Adjustment[]
   *   The adjustments.
   *
   * @see \Drupal\commerce_order\AdjustmentTransformerInterface::processAdjustments()
   */
  public function collectAdjustments(array $adjustment_types = []);

  /**
   * Gets the invoice subtotal price.
   *
   * Represents a sum of all invoice item totals.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The invoice subtotal price, or NULL.
   */
  public function getSubtotalPrice();

  /**
   * Recalculates the invoice total price.
   *
   * @return $this
   */
  public function recalculateTotalPrice();

  /**
   * Gets the invoice total price.
   *
   * Represents a sum of all invoice item totals.
   *
   * @return \Drupal\commerce_price\Price|null
   *   The invoice total price, or NULL.
   */
  public function getTotalPrice();

  /**
   * Gets the invoice state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The invoice state.
   */
  public function getState();

  /**
   * Gets the invoice creation timestamp.
   *
   * @return int
   *   Creation timestamp of the invoice.
   */
  public function getCreatedTime();

  /**
   * Sets the invoice creation timestamp.
   *
   * @param int $timestamp
   *   The invoice creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the invoice due date.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The invoice due date.
   */
  public function getDueDate();

  /**
   * Sets the invoice due date.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $due_date
   *   The invoice due date.
   *
   * @return $this
   */
  public function setDueDate(DrupalDateTime $due_date);

}
