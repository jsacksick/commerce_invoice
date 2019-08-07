<?php

namespace Drupal\commerce_invoice\Entity;

use Drupal\commerce\CommerceSinglePluginCollection;
use Drupal\commerce\Entity\CommerceBundleEntityBase;
use Drupal\commerce_number_pattern\Entity\NumberPattern;

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
 *     "numberPattern",
 *     "footerText",
 *     "paymentTerms",
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
   * The number pattern entity.
   *
   * @var \Drupal\commerce_number_pattern\Entity\NumberPatternInterface
   */
  protected $numberPattern;

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
   * The invoice type workflow ID.
   *
   * @var string
   */
  protected $workflow;

  /**
   * {@inheritdoc}
   */
  public function getNumberPattern() {
    if ($this->getNumberPatternId()) {
      return NumberPattern::load($this->getNumberPatternId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberPatternId() {
    return $this->numberPattern;
  }

  /**
   * {@inheritdoc}
   */
  public function setNumberPatternId($number_pattern) {
    $this->numberPattern = $number_pattern;
    return $this;
  }

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

}
