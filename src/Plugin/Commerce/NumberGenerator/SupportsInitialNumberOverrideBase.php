<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a base class for number generators which support overriding the initial number generated.
 */
abstract class SupportsInitialNumberOverrideBase extends NumberGeneratorBase implements SupportsInitialNumberOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'initialNumber' => '1',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getInitialNumber() {
    return $this->configuration['initialNumber'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['initialNumber'] = [
      '#type' => 'number',
      '#title' => $this->t('Initial invoice number'),
      '#description' => $this->t('Overrides the initial invoice number (Defaults to 1). Changing this setting once the first invoice has been issued has no effect.'),
      '#default_value' => $this->configuration['initialNumber'],
      '#min' => 1,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['initialNumber'] = $values['initialNumber'];
    }
  }

}
