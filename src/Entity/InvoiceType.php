<?php

namespace Drupal\commerce_invoice\Entity;

use Drupal\commerce\CommerceSinglePluginCollection;
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
 *       "duplicate" = "Drupal\commerce_invoice\Form\InvoiceTypeForm",
 *       "edit" = "Drupal\commerce_invoice\Form\InvoiceTypeForm",
 *       "delete" = "Drupal\commerce\Form\CommerceBundleEntityDeleteFormBase"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
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
 *     "footerText",
 *     "paymentTerms",
 *     "numberGenerator",
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
   * The invoice type footer text.
   *
   * @var string
   */
  protected $footerText;

  /**
   * The invoice type payment terms.
   *
   * @var string
   */
  protected $paymentTerms;

  /**
   * The number generator plugin ID.
   *
   * @var string
   */
  protected $numberGenerator = 'infinite';

  /**
   * The number generator plugin configuration.
   *
   * @var array
   */
  protected $numberGeneratorConfiguration = [];


  /**
   * The plugin collection that holds the number generator plugin.
   *
   * @var \Drupal\commerce\CommerceSinglePluginCollection
   */
  protected $numberGeneratorPluginCollection;

  /**
   * The invoice type workflow ID.
   *
   * @var string
   */
  protected $workflow;

  /**
   * {@inheritdoc}
   */
  public function getFooterText() {
    return $this->footerText;
  }

  /**
   * {@inheritdoc}
   */
  public function setFooterText($footer_text) {
    $this->footerText = $footer_text;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentTerms() {
    return $this->paymentTerms;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentTerms($payment_terms) {
    $this->paymentTerms = $payment_terms;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberGeneratorId() {
    return $this->numberGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function setNumberGeneratorId($number_generator_id) {
    $this->numberGenerator = $number_generator_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberGenerator() {
    return $this->getNumberGeneratorPluginCollection()->get($this->numberGenerator);
  }

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

    // The invoice type must depend on the module that provides the workflow.
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    $workflow = $workflow_manager->createInstance($this->getWorkflowId());
    $this->calculatePluginDependencies($workflow);

    return $this;
  }

  /**
   * Gets the plugin collection that holds the number generator plugin.
   *
   * Ensures the plugin collection is initialized before returning it.
   *
   * @return \Drupal\commerce\CommerceSinglePluginCollection
   *   The plugin collection.
   */
  protected function getNumberGeneratorPluginCollection() {
    if (!$this->numberGeneratorPluginCollection) {
      $plugin_manager = \Drupal::service('plugin.manager.commerce_number_generator');
      $this->numberGeneratorPluginCollection = new CommerceSinglePluginCollection($plugin_manager, $this->numberGenerator, $this->numberGeneratorConfiguration, $this->id);
    }
    return $this->numberGeneratorPluginCollection;
  }

}
