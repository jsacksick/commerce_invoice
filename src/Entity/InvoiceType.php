<?php

namespace Drupal\commerce_invoice\Entity;

use Drupal\commerce\Entity\CommerceBundleEntityBase;

/**
 * Defines the invoice type entity class.
 *
 * @ConfigEntityType(
 *   id = "commerce_invoice_type",
 *   label = @Translation("Invoice type"),
 *   label_collection = @Translation("Invoice types"),
 *   label_singular = @Translation("invoice type"),
 *   label_plural = @Translation("invoice types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count invoice type",
 *     plural = "@count invoice types",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\commerce\CommerceBundleAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\commerce_invoice\Form\InvoiceTypeForm",
 *       "edit" = "Drupal\commerce_invoice\Form\InvoiceTypeForm",
 *       "delete" = "Drupal\commerce\Form\CommerceBundleEntityDeleteFormBase"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "list_builder" = "Drupal\commerce_invoice\InvoiceTypeListBuilder",
 *   },
 *   admin_permission = "administer commerce_invoice_type",
 *   config_prefix = "commerce_invoice_type",
 *   bundle_of = "commerce_invoice",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "label",
 *     "id",
 *     "workflow",
 *     "traits",
 *   },
 *   links = {
 *     "add-form" = "/admin/commerce/config/invoice-types/add",
 *     "edit-form" = "/admin/commerce/config/invoice-types/{commerce_invoice_type}/edit",
 *     "duplicate-form" = "/admin/commerce/config/invoice-types/{commerce_invoice_type}/duplicate",
 *     "delete-form" = "/admin/commerce/config/invoice-types/{commerce_invoice_type}/delete",
 *     "collection" = "/admin/commerce/config/invoice-types"
 *   }
 * )
 */
class InvoiceType extends CommerceBundleEntityBase implements InvoiceTypeInterface {

  /**
   * The invoice type workflow ID.
   *
   * @var string
   */
  protected $workflow;

  /**
   * {@inheritdoc}
   */
  public function getWorkflowId() {
    return $this->workflow;
  }

  /**
   * {@inheritdoc}
   */
  public function setWorkflowId($workflow_id) {
    $this->workflow = $workflow_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // The order type must depend on the module that provides the workflow.
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    $workflow = $workflow_manager->createInstance($this->getWorkflowId());
    $this->calculatePluginDependencies($workflow);

    return $this;
  }

}
