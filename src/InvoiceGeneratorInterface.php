<?php

namespace Drupal\commerce_invoice;

interface InvoiceGeneratorInterface {

  /**
   * Generate an invoice for the given orders.
   *
   * This logic assumes the orders passed are of the same type.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface[] $orders
   *   The orders to generate an invoice for.
   *
   * @return \Drupal\commerce_invoice\Entity\InvoiceInterface
   *   The generated invoice.
   */
  public function generate(array $orders);

}
