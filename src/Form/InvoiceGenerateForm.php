<?php

namespace Drupal\commerce_invoice\Form;

use Drupal\commerce_invoice\InvoiceGeneratorInterface;
use Drupal\commerce_order\Form\CustomerFormTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the invoice generate form.
 */
class InvoiceGenerateForm extends FormBase {

  use CustomerFormTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  protected $entityTypeManager;

  /**
   * The invoice generator.
   *
   * @var \Drupal\commerce_invoice\InvoiceGeneratorInterface
   */
  protected $invoiceGenerator;

  /**
   * Constructs a new InvoiceGenerateForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_invoice\InvoiceGeneratorInterface $invoice_generator
   *   The invoice generator.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, InvoiceGeneratorInterface $invoice_generator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->invoiceGenerator = $invoice_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('commerce_invoice.invoice_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_invoice_generate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['orders'] = [
      '#type' => 'commerce_entity_select',
      '#title' => $this->t('Orders'),
      '#target_type' => 'commerce_order',
      '#multiple' => TRUE,
      '#required' => TRUE,
    ];
    $form['store_id'] = [
      '#type' => 'commerce_entity_select',
      '#title' => $this->t('Store'),
      '#target_type' => 'commerce_store',
      '#required' => TRUE,
    ];
    $form = $this->buildCustomerForm($form, $form_state);
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->submitCustomerForm($form, $form_state);

    $values = $form_state->getValues();
    /** @var \Drupal\commerce_order\OrderStorage $order_storage */
    $order_storage = $this->entityTypeManager->getStorage('commerce_order');
    $orders = $order_storage->loadMultiple($values['orders']);
    /** @var \Drupal\commerce_order\Entity\OrderInterface $first_order */
    $first_order = reset($orders);
    $invoice_values = [
      'store_id' => $values['store_id'],
      'uid' => $values['uid'],
    ];
    $this->invoiceGenerator->generate($orders, $first_order->getBillingProfile(), $invoice_values);
    $form_state->setRedirect('entity.commerce_invoice.collection');
  }

}