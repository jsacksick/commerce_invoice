<?php

namespace Drupal\commerce_invoice\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the number generator plugin annotation object.
 *
 * Plugin namespace: Plugin\Commerce\NumberGenerator.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class CommerceNumberGenerator extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
