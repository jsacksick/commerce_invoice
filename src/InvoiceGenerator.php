<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class InvoiceGenerator implements InvoiceGeneratorInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new InvoiceGenerator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function generate(array $orders) {
    $invoice_storage = $this->entityTypeManager->getStorage('commerce_invoice');
    $invoice_item_storage = $this->entityTypeManager->getStorage('commerce_invoice_item');
    // Assume the order type from the first passed order, we'll use it
    // to determine the invoice type to create.
    $first_order = reset($orders);
    /** @var \Drupal\commerce_order\Entity\OrderTypeInterface $order_type */
    $order_type = OrderType::load($first_order->bundle());
    $invoice_type = $order_type->getThirdPartySetting('commerce_invoice', 'invoice_type', 'default');
    /** @var \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice */
    $invoice = $invoice_storage->create(['type' => $invoice_type]);

    foreach ($orders as $order) {
      foreach ($order->getAdjustments() as $adjustment) {
        $invoice->addAdjustment($adjustment);
      }
      foreach ($order->getItems() as $order_item) {
        /** @var \Drupal\commerce_order\Entity\OrderItemTypeInterface $order_item_type */
        $order_item_type = OrderItemType::load($order_item->bundle());
        $invoice_item_type = $order_item_type->getPurchasableEntityTypeId() ?: 'default';
        /** @var \Drupal\commerce_invoice\Entity\InvoiceItemInterface $invoice_item */
        $invoice_item = $invoice_item_storage->create(['type' => $invoice_item_type]);
        $invoice_item->populateFromOrderItem($order_item);
        $invoice_item->save();
        $invoice->addItem($invoice_item);
      }
    }
    $invoice->setInvoiceNumber(mt_rand(1, 100));
    $invoice->save();
    return $invoice;
  }

}
