<?php

namespace Drupal\commerce_invoice\Entity;

use Drupal\commerce\Entity\CommerceBundleEntityBase;

/**
 * Defines the shipment type entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_invoice_item_type",
 *   label = @Translation("Invoice item type"),
 *   label_collection = @Translation("Invoice item types"),
 *   label_singular = @Translation("invoice item type"),
 *   label_plural = @Translation("invoice item types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count invoice item type",
 *     plural = "@count invoice item types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_invoice\InvoiceItemTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_invoice\Form\InvoiceItemTypeForm",
 *       "edit" = "Drupal\commerce_invoice\Form\InvoiceItemTypeForm",
 *       "delete" = "Drupal\commerce\Form\CommerceBundleEntityDeleteFormBase"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer commerce_invoice_type",
 *   config_prefix = "commerce_invoice_item_type",
 *   bundle_of = "commerce_invoice_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "traits",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/invoice-item-types/add",
 *     "edit-form" = "/admin/commerce/config/invoice-item-types/{commerce_invoice_item_type}/edit",
 *     "delete-form" = "/admin/commerce/config/invoice-item-types/{commerce_invoice_item_type}/delete",
 *     "collection" = "/admin/commerce/config/invoice-item-types",
 *   }
 * )
 */
class InvoiceItemType extends CommerceBundleEntityBase implements InvoiceItemTypeInterface {

}
