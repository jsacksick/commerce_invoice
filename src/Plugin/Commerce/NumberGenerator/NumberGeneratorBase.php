<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\InvoiceNumberSequence;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the base class for number generator plugins.
 */
abstract class NumberGeneratorBase extends PluginBase implements NumberGeneratorInterface, ContainerFactoryPluginInterface {

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a new NumberGeneratorBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeInterface $time, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->time = $time;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('datetime.time'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPattern() {
    return $this->configuration['pattern'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPadding() {
    return $this->configuration['padding'];
  }

  /**
   * {@inheritdoc}
   */
  public function generate(InvoiceInterface $invoice, InvoiceNumberSequence $invoice_number_sequence) {
    $sequence = $invoice_number_sequence->getSequence();
    if ($this->configuration['padding'] > 0) {
      $sequence = str_pad($sequence, $this->configuration['padding'], '0', STR_PAD_LEFT);
    }
    $sequence = str_replace('{number}', $sequence, $this->configuration['pattern']);
    return $this->token->replace($sequence, ['commerce_invoice' => $invoice]);
  }

  /**
   * {@inheritdoc}
   */
  public function shouldReset(InvoiceNumberSequence $last_sequence) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'pattern' => '{number}',
      'padding' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $token_types = ['commerce_invoice'];
    $form['pattern'] = [
      '#title' => $this->t('Pattern'),
      '#type' => 'textfield',
      '#description' => $this->t('In addition to the generation method, a pattern for the invoice number can be set, e.g. to pre- or suffix the calculated number. The placeholder "{number}" is replaced with the generated number and *must* be included in the pattern. Tokens can be used.'),
      '#default_value' => $this->configuration['pattern'],
      '#required' => TRUE,
      '#element_validate' => ['token_element_validate'],
      '#token_types' => $token_types,
    ];
    $form['pattern_help'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => $token_types,
    ];
    $form['padding'] = [
      '#type' => 'number',
      '#title' => $this->t('Invoice number padding'),
      '#description' => $this->t('Pad the invoice number with leading zeroes. Example: a value of 6 will output invoice id 52 as 000052.'),
      '#default_value' => $this->configuration['padding'],
      '#min' => 0,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    if (strpos($values['pattern'], '{number}') === FALSE) {
      $form_state->setError($form['pattern'], t('Missing the required placeholder {number}.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);

      $this->configuration = [];
      $this->configuration['pattern'] = $values['pattern'];
      $this->configuration['padding'] = $values['padding'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

}
