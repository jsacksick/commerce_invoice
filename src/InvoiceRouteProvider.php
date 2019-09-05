<?php

namespace Drupal\commerce_invoice;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for the Invoice entity.
 */
class InvoiceRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);
    $entity_type_id = $entity_type->id();

    if ($order_collection_route = $this->getOrderCollectionRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.order_collection", $order_collection_route);
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  protected function getOrderCollectionRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('order-collection') && ($admin_permission = $entity_type->getAdminPermission())) {
      /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $label */
      $label = $entity_type->getCollectionLabel();

      $route = new Route($entity_type->getLinkTemplate('order-collection'));
      $route
        ->addDefaults([
          '_entity_list' => $entity_type->id(),
          '_title' => $label->getUntranslatedString(),
          '_title_arguments' => $label->getArguments(),
          '_title_context' => $label->getOption('context'),
        ])
        ->setOption('parameters', [
          'commerce_order' => [
            'type' => 'entity:commerce_order',
          ],
        ])
        ->setRequirement('_permission', $admin_permission);

      return $route;
    }
  }

}
