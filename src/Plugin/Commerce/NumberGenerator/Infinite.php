<?php

namespace Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator;

use Drupal\Core\Annotation\Translation;

/**
 * Provides an infinite number generator.
 *
 * @CommerceNumberGenerator(
 *   id = "infinite",
 *   label = @Translation("Infinite (one single number, that is never reset, and incremented at each invoice generation)"),
 * )
 */
class Infinite extends NumberGeneratorBase {

}
