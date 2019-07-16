<?php

namespace Drupal\commerce_invoice;

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
    /** @var \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice */
    // @todo: Define a type based on the order type.
    $invoice = $invoice_storage->create(['type' => 'default']);

    /** @var \Drupal\commerce_order\Entity\OrderInterface[] $orders */
    foreach ($orders as $order) {
      foreach ($order->getAdjustments() as $adjustment) {
        $invoice->addAdjustment($adjustment);
      }
      foreach ($order->getItems() as $order_item) {
        // @todo: Figure out how to determine the invoice item type.
        /** @var \Drupal\commerce_invoice\Entity\InvoiceItemInterface $invoice_item */
        $invoice_item = $invoice_item_storage->create(['type' => 'default']);
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
