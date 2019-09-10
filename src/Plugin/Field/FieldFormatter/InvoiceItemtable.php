<?php

namespace Drupal\commerce_invoice\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'commerce_invoice_item_table' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_invoice_item_table",
 *   label = @Translation("Order item table"),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class InvoiceItemTable extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\commerce_invoice\Entity\InvoiceInterface $order */
    $invoice = $items->getEntity();
    $elements = [];
    $elements[0] = [
      '#type' => 'view',
      '#name' => 'commerce_invoice_item_table',
      '#arguments' => [$invoice->id()],
      '#embed' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = $field_definition->getTargetEntityTypeId();
    $field_name = $field_definition->getName();
    return $entity_type == 'commerce_invoice' && $field_name == 'invoice_items';
  }

}
