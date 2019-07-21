<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce\CommerceContentEntityStorage;
use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\Event\InvoiceEvent;
use Drupal\commerce_invoice\Event\InvoiceEvents;
use Drupal\Core\Entity\EntityInterface;

class InvoiceStorage extends CommerceContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  protected function invokeHook($hook, EntityInterface $entity) {
    if ($hook == 'presave') {
      // Invoice::preSave() has completed, now run the storage-level pre-save
      // tasks. These tasks can modify the invoice, so they need to run
      // before the entity/field hooks are invoked.
      $this->doInvoicePresave($entity);
    }

    parent::invokeHook($hook, $entity);
  }

  /**
   * Performs invoice-specific pre-save tasks.
   *
   * This includes:
   * - Recalculating the total price.
   * - Dispatching the "invoice paid" event.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice
   *   The invoice.
   */
  protected function doInvoicePresave(InvoiceInterface $invoice) {
    $invoice->recalculateTotalPrice();

    // Notify other modules if the invoice has been fully paid.
    $original_paid = isset($invoice->original) ? $invoice->original->isPaid() : FALSE;
    if ($invoice->isPaid() && !$original_paid) {
      // Invoice::preSave() initializes the 'paid_event_dispatched' flag to
      // FALSE.
      // Skip dispatch if it already happened once (flag is TRUE).
      if ($invoice->getData('paid_event_dispatched') === FALSE) {
        $event = new InvoiceEvent($invoice);
        $this->eventDispatcher->dispatch(InvoiceEvents::INVOICE_PAID, $event);
        $invoice->setData('paid_event_dispatched', TRUE);
      }
    }
  }

}
