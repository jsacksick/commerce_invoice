<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\InvoiceNumberSequence;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('datetime.time')
    );
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
  public function generate(InvoiceInterface $invoice, InvoiceNumberSequence $invoice_number_sequence) {
    return $invoice_number_sequence->getSequence();
  }

}
