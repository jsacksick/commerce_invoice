<?php

namespace Drupal\commerce_invoice\Form;

use Drupal\commerce\EntityTraitManagerInterface;
use Drupal\commerce\Form\CommerceBundleEntityFormBase;
use Drupal\commerce_invoice\NumberGeneratorManager;
use Drupal\Component\Utility\Html;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Form\EntityDuplicateFormTrait;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\state_machine\WorkflowManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an invoice type form.
 */
class InvoiceTypeForm extends CommerceBundleEntityFormBase {

  use EntityDuplicateFormTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The number generator plugin manager.
   *
   * @var \Drupal\commerce_invoice\NumberGeneratorManager
   */
  protected $numberGeneratorPluginManager;

  /**
   * The workflow manager.
   *
   * @var \Drupal\state_machine\WorkflowManagerInterface
   */
  protected $workflowManager;

  /**
   * Constructs a new InvoiceTypeForm object.
   *
   * @param \Drupal\commerce\EntityTraitManagerInterface $trait_manager
   *   The entity trait manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\commerce_invoice\NumberGeneratorManager $number_generator_manager
   *   The number generator plugin manager.
   * @param \Drupal\state_machine\WorkflowManagerInterface $workflow_manager
   *   The workflow manager.
   */
  public function __construct(EntityTraitManagerInterface $trait_manager, ModuleHandlerInterface $module_handler, NumberGeneratorManager $number_generator_manager, WorkflowManagerInterface $workflow_manager) {
    parent::__construct($trait_manager);
    $this->moduleHandler = $module_handler;
    $this->numberGeneratorPluginManager = $number_generator_manager;
    $this->workflowManager = $workflow_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.commerce_entity_trait'),
      $container->get('module_handler'),
      $container->get('plugin.manager.commerce_number_generator'),
      $container->get('plugin.manager.workflow')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\commerce_invoice\Entity\InvoiceTypeInterface $invoice_type */
    $invoice_type = $this->entity;
    $workflows = $this->workflowManager->getGroupedLabels('commerce_invoice');
    $number_generators = array_column($this->numberGeneratorPluginManager->getDefinitions(), 'label', 'id');
    asort($number_generators);
    // The form state will have a plugin value if #ajax was used.
    $number_generator = $form_state->getValue('numberGenerator', $invoice_type->getNumberGeneratorId());

    $wrapper_id = Html::getUniqueId('invoice-type-form');
    $form['#tree'] = TRUE;
    $form['#prefix'] = '<div id="' . $wrapper_id . '">';
    $form['#suffix'] = '</div>';

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $invoice_type->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $invoice_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_invoice\Entity\InvoiceType::load',
        'source' => ['label'],
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => !$invoice_type->isNew(),
    ];
    $token_types = ['commerce_invoice'];
    $token_exists = $this->moduleHandler->moduleExists('token');
    $form['footerText'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Footer text'),
      '#default_value' => $invoice_type->getFooterText(),
      '#description' => $this->t('Text to display in the footer of the invoice.'),
    ];
    // Allow inserting tokens if the token module exists.
    if ($token_exists) {
      $form['footerText'] += [
        '#element_validate' => ['token_element_validate'],
        '#token_types' => $token_types,
      ];
      $form['footer_text_token_help'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => $token_types,
      ];
    }
    $form['paymentTerms'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Payment terms'),
      '#default_value' => $invoice_type->getPaymentTerms(),
      '#description' => $this->t('The payment terms.'),
      '#element_validate' => ['token_element_validate'],
      '#token_types' => $token_types,
    ];
    if ($token_exists) {
      $form['paymentTerms'] += [
        '#element_validate' => ['token_element_validate'],
        '#token_types' => $token_types,
      ];
      $form['payment_terms_token_help'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => $token_types,
      ];
    }
    $form['numberGenerator'] = [
      '#type' => 'radios',
      '#title' => $this->t('Number generator'),
      '#options' => $number_generators,
      '#default_value' => $number_generator,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxRefresh',
        'wrapper' => $wrapper_id,
      ],
    ];
    $form['workflow'] = [
      '#type' => 'select',
      '#title' => $this->t('Workflow'),
      '#options' => $workflows,
      '#default_value' => $invoice_type->getWorkflowId(),
      '#description' => $this->t('Used by all invoices of this type.'),
    ];
    $form = $this->buildTraitForm($form, $form_state);

    return $form;
  }

  /**
   * Ajax callback.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateTraitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
    $this->postSave($this->entity, $this->operation);
    $this->submitTraitForm($form, $form_state);
    $this->messenger()->addMessage($this->t('Saved the %label invoice type.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.commerce_invoice_type.collection');
  }

}
