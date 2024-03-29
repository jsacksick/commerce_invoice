<?php

namespace Drupal\commerce_invoice\Form;

use Drupal\commerce\EntityHelper;
use Drupal\commerce\EntityTraitManagerInterface;
use Drupal\commerce\Form\CommerceBundleEntityFormBase;
use Drupal\Component\Utility\Html;
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
   * @param \Drupal\state_machine\WorkflowManagerInterface $workflow_manager
   *   The workflow manager.
   */
  public function __construct(EntityTraitManagerInterface $trait_manager, WorkflowManagerInterface $workflow_manager) {
    parent::__construct($trait_manager);
    $this->workflowManager = $workflow_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.commerce_entity_trait'),
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
    $wrapper_id = Html::getUniqueId('invoice-type-form');
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
    $form['workflow'] = [
      '#type' => 'select',
      '#title' => $this->t('Workflow'),
      '#options' => $workflows,
      '#default_value' => $invoice_type->getWorkflowId(),
      '#description' => $this->t('Used by all invoices of this type.'),
    ];

    // Load number patterns.
    $storage = $this->entityTypeManager->getStorage('commerce_number_pattern');
    $query = $storage->getQuery();
    $query->condition('targetEntityType', 'commerce_invoice');
    $number_patterns = $query->execute();
    $options = [];
    if ($number_patterns) {
      $number_patterns = $storage->loadMultiple($number_patterns);
      $options = EntityHelper::extractLabels($number_patterns);
    }
    $form['numberPattern'] = [
      '#type' => 'select',
      '#default_value' => $invoice_type->getNumberPatternId() ?: key($options),
      '#title' => $this->t('Number pattern'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['logo_file'] = [
      '#title' => $this->t('Logo'),
      '#type' => 'managed_file',
      '#upload_location' => 'public://commerce_invoice_type_icon/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg svg'],
      ],
    ];

    if ($file = $invoice_type->getLogoFile()) {
      $form['logo_file']['#default_value'] = ['target_id' => $file->id()];
    }

    $token_types = ['commerce_invoice'];
    $form['footerText'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Footer text'),
      '#default_value' => $invoice_type->getFooterText(),
      '#description' => $this->t('Text to display in the footer of the invoice.'),
      '#element_validate' => ['token_element_validate'],
      '#token_types' => $token_types,
    ];
    $form['footer_text_token_help'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => $token_types,
    ];
    $form['payment-terms'] = [
      '#type' => 'details',
      '#title' => $this->t('Payment terms'),
      '#tree' => FALSE,
      '#open' => TRUE,
    ];
    $form['payment-terms']['dueDays'] = [
      '#type' => 'number',
      '#size' => 3,
      '#description' => $this->t("used to determine the invoice's due date."),
      '#field_suffix' => $this->t('days'),
      '#title' => $this->t('Due date'),
      '#default_value' => $invoice_type->getDueDays(),
    ];
    $form['payment-terms']['paymentTerms'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Payment terms'),
      '#default_value' => $invoice_type->getPaymentTerms(),
      '#description' => $this->t('The payment terms.'),
      '#element_validate' => ['token_element_validate'],
      '#token_types' => $token_types,
    ];
    $form['payment-terms']['payment_terms_token_help'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => $token_types,
    ];
    $form = $this->buildTraitForm($form, $form_state);

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => $this->t('Language settings'),
      ];
      $form['language']['language_configuration'] = [
        '#type' => 'commerce_invoice_language_configuration',
        '#entity_information' => [
          'entity_type' => 'commerce_invoice',
          'bundle' => $invoice_type->id(),
        ],
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('commerce_invoice', $invoice_type->id()),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateTraitForm($form, $form_state);
    /** @var \Drupal\commerce_invoice\Entity\InvoiceTypeInterface $invoice_type */
    $invoice_type = $this->entity;

    $logo_file = $form_state->getValue(['logo_file', '0']);
    if (!empty($logo_file) && $file = $this->entityTypeManager->getStorage('file')->load($logo_file)) {
      $invoice_type->setLogo($file->uuid());
    }
    else {
      $invoice_type->setLogo(NULL);
    }
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
