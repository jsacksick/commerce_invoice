<?php

namespace Drupal\Tests\commerce_invoice\Traits;

use Drupal\commerce_invoice\Entity\Invoice;
use Drupal\commerce_invoice\Entity\InvoiceType;
use Drupal\commerce_store\Entity\StoreInterface;

/**
 * Helper for invoice test classes.
 */
trait InvoiceTestTrait {

  /**
   * Creates a test invoice.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store.
   * @param string $type
   *   (optional) The invoice type.
   * @param array $values
   *   (optional) An array of values to set, keyed by property name.
   * @param bool $skip_save
   *   (optional) Whether to skip saving the invoice. Defaults to FALSE.
   *
   * @return \Drupal\commerce_invoice\Entity\InvoiceInterface
   *   The created invoice.
   */
  protected function createInvoice(StoreInterface $store, $type = 'default', array $values = [], $skip_save = FALSE) {
    $values = [
      'store_id' => $store->id(),
      'type' => $type,
    ] + $values;
    $invoice = Invoice::create($values);

    if (!$skip_save) {
      $invoice->save();
    }

    return $invoice;
  }

  /**
   * Creates a test invoice type.
   *
   * @param string $id
   *   (optional) The invoice type machine name.
   * @param string $label
   *   (optional) The invoice type label.
   * @param array $values
   *   (optional) An array of values to set, keyed by property name.
   *
   * @return \Drupal\commerce_invoice\Entity\InvoiceTypeInterface
   *   The created invoice type.
   */
  protected function createInvoiceType($id = NULL, $label = NULL, array $values = []) {
    $id = !empty($id) ? $id : $this->randomMachineName();
    $label = !empty($label) ? $label : $this->randomMachineName();
    $values += [
      'id' => $id,
      'label' => $label,
      'workflow' => 'invoice_default',
    ];
    $invoice_type = InvoiceType::create($values);
    $invoice_type->save();
    return $invoice_type;
  }

}
