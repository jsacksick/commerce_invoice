<?php

namespace Drupal\commerce_invoice_test\EventSubscriber;

use Drupal\commerce_invoice\Event\InvoiceEvent;
use Drupal\commerce_invoice\Event\InvoiceEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoicePaidSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      InvoiceEvents::INVOICE_PAID => 'onPaid',
    ];
  }

  /**
   * Increments an invoice flag each time the paid event gets dispatched.
   *
   * @param \Drupal\commerce_invoice\Event\InvoiceEvent $event
   *   The event.
   */
  public function onPaid(InvoiceEvent $event) {
    $invoice = $event->getInvoice();
    $flag = $invoice->getData('invoice_test_called', 0);
    $flag++;
    $invoice->setData('invoice_test_called', $flag);
  }

}
