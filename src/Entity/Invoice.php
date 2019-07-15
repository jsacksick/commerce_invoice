<?php

namespace Drupal\commerce_invoice\Entity;

use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\profile\Entity\ProfileInterface;
use Drupal\user\Entity\User;
use Drupal\user\EntityOwnerTrait;
use Drupal\user\UserInterface;

/**
 * Defines the invoice entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_invoice",
 *   label = @Translation("Invoice"),
 *   label_collection = @Translation("Invoices"),
 *   label_singular = @Translation("invoice"),
 *   label_plural = @Translation("invoices"),
 *   label_count = @PluralTranslation(
 *     singular = "@count invoice",
 *     plural = "@count invoices",
 *   ),
 *   bundle_label = @Translation("Invoice type"),
 *   handlers = {
 *     "event" = "Drupal\commerce_invoice\Event\InvoiceEvent",
 *     "storage" = "Drupal\commerce\CommerceContentEntityStorage",
 *     "views_data" = "Drupal\commerce\CommerceEntityViewsData",
 *     "form" = {
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *   },
 *   base_table = "commerce_invoice",
 *   admin_permission = "administer commerce_invoice",
 *   permission_granularity = "bundle",
 *   entity_keys = {
 *     "id" = "invoice_id",
 *     "label" = "invoice_number",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   },
 *   bundle_entity_type = "commerce_invoice_type",
 *   field_ui_base_route = "entity.commerce_invoice_type.edit_form"
 * )
 */
class Invoice extends CommerceContentEntityBase implements InvoiceInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getInvoiceNumber() {
    return $this->get('invoice_number')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setInvoiceNumber($invoice_number) {
    $this->set('invoice_number', $invoice_number);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStore() {
    return $this->getTranslatedReferencedEntity('store_id');
  }

  /**
   * {@inheritdoc}
   */
  public function setStore(StoreInterface $store) {
    $this->set('store_id', $store->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStoreId() {
    return $this->get('store_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setStoreId($store_id) {
    $this->set('store_id', $store_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomer() {
    $customer = $this->get('uid')->entity;
    // Handle deleted customers.
    if (!$customer) {
      $customer = User::getAnonymousUser();
    }
    return $customer;
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomer(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCustomerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }
  /**
   * {@inheritdoc}
   */
  public function getBillingProfile() {
    return $this->get('billing_profile')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setBillingProfile(ProfileInterface $profile) {
    $this->set('billing_profile', $profile);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems() {
    return $this->get('invoice_items')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function setItems(array $invoice_items) {
    $this->set('invoice_items', $invoice_items);
    $this->recalculateTotalPrice();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasItems() {
    return !$this->get('invoice_items')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function addItem(InvoiceItemInterface $invoice_item) {
    if (!$this->hasItem($invoice_item)) {
      $this->get('invoice_items')->appendItem($invoice_item);
      $this->recalculateTotalPrice();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeItem(InvoiceItemInterface $invoice_item) {
    $index = $this->getItemIndex($invoice_item);
    if ($index !== FALSE) {
      $this->get('invoice_items')->offsetUnset($index);
      $this->recalculateTotalPrice();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasItem(InvoiceItemInterface $invoice_item) {
    return $this->getItemIndex($invoice_item) !== FALSE;
  }

  /**
   * Gets the index of the given invoice item.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceItemInterface $invoice_item
   *   The invoice item.
   *
   * @return int|bool
   *   The index of the given invoice item, or FALSE if not found.
   */
  protected function getItemIndex(InvoiceItemInterface $invoice_item) {
    $values = $this->get('invoice_items')->getValue();
    $invoice_item_ids = array_map(function ($value) {
      return $value['target_id'];
    }, $values);

    return array_search($invoice_item->id(), $invoice_item_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function recalculateTotalPrice() {
    /** @var \Drupal\commerce_price\Price $total_price */
    $total_price = NULL;
    foreach ($this->getItems() as $invoice_item) {
      if ($invoice_item_total = $invoice_item->getTotalPrice()) {
        $total_price = $total_price ? $total_price->add($invoice_item_total) : $invoice_item_total;
      }
    }
    $this->total_price = $total_price;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTotalPrice() {
    if (!$this->get('total_price')->isEmpty()) {
      return $this->get('total_price')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->get('state')->first();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Ensure there's a back-reference on each invoice item.
    foreach ($this->getItems() as $invoice_item) {
      if ($invoice_item->invoice_id->isEmpty()) {
        $invoice_item->invoice_id = $this->id();
        $invoice_item->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Delete the invoice items of a deleted invoice.
    $invoice_items = [];
    /** @var \Drupal\commerce_invoice\Entity\InvoiceInterface $entity */
    foreach ($entities as $entity) {
      foreach ($entity->getItems() as $invoice_item) {
        $invoice_items[$invoice_item->id()] = $invoice_item;
      }
    }
    if (!$invoice_items) {
      return;
    }
    $invoice_item_storage = \Drupal::service('entity_type.manager')->getStorage('commerce_invoice_item');
    $invoice_item_storage->delete($invoice_items);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['invoice_number'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Invoice number'))
      ->setDescription(t('The invoice number.'))
      ->setRequired(TRUE)
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['store_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Store'))
      ->setDescription(t('The store to which the invoice belongs.'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'commerce_store')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['uid']
      ->setLabel(t('Customer'))
      ->setDescription(t('The customer.'));

    $fields['billing_profile'] = BaseFieldDefinition::create('entity_reference_revisions')
      ->setLabel(t('Billing information'))
      ->setDescription(t('Billing profile'))
      ->setSetting('target_type', 'profile')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', ['target_bundles' => ['customer']])
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['invoice_items'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Invoice items'))
      ->setDescription(t('The invoice items.'))
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'commerce_invoice_item')
      ->setSetting('handler', 'default')
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['total_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Total price'))
      ->setDescription(t('The total price of the invoice.'))
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['state'] = BaseFieldDefinition::create('state')
      ->setLabel(t('State'))
      ->setDescription(t('The invoice state.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE)
      ->setSetting('workflow_callback', ['\Drupal\commerce_invoice\Entity\Invoice', 'getWorkflowId']);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the invoice was created.'));

    return $fields;
  }

  /**
   * Gets the workflow ID for the state field.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice
   *   The invoice.
   *
   * @return string
   *   The workflow ID.
   */
  public static function getWorkflowId(InvoiceInterface $invoice) {
    $workflow = InvoiceType::load($invoice->bundle())->getWorkflowId();
    return $workflow;
  }

}
