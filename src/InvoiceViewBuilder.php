<?php

namespace Drupal\commerce_invoice;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for invoice entities.
 */
class InvoiceViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    // The Entity print module doesn't pass the entity language to the entity
    // view builder, so this is done to ensure the invoice is rendered in the
    // correct language.
    if (!$langcode && $language = $entity->language()) {
      $langcode = $language->getId();
    }
    return parent::view($entity, $view_mode, $langcode);
  }

}
