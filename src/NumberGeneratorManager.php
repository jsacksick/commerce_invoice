<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Annotation\CommerceNumberGenerator;
use Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages discovery and instantiation of number generator plugins.
 *
 * @see \Drupal\commerce_invoice\Annotation\CommerceNumberGenerator
 * @see plugin_api
 */
class NumberGeneratorManager extends DefaultPluginManager {

  /**
   * Constructs a new NumberGeneratorManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Commerce/NumberGenerator', $namespaces, $module_handler, NumberGeneratorInterface::class, CommerceNumberGenerator::class
    );

    $this->alterInfo('commerce_number_generator_info');
    $this->setCacheBackend($cache_backend, 'commerce_number_generator_plugins');
  }

}
