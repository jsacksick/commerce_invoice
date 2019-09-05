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

    if ($invoice_payment_route = $this->getInvoicePaymentFormRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.payment_form", $invoice_payment_route);
    }
    if ($order_collection_route = $this->getOrderCollectionRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.order_collection", $order_collection_route);
    }

    return $collection;
  }

  /**
   * Gets the invoice payment-form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getInvoicePaymentFormRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('payment-form')) {
      $route = new Route($entity_type->getLinkTemplate('payment-form'));
      $entity_type_id = $entity_type->id();
      $route
        ->setDefaults([
          '_form' => '\Drupal\commerce_invoice\Form\InvoicePaymentForm',
        ])
        ->setRequirement('_entity_access', "{$entity_type_id}.update")
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ]);

      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }

      return $route;
    }
  }

  /**
   * Gets the order-collection route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
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
