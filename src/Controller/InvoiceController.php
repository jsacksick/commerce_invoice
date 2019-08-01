<?php

namespace Drupal\commerce_invoice\Controller;

use Drupal\commerce_invoice\InvoiceGeneratorInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the invoice generate page.
 */
class InvoiceController implements ContainerInjectionInterface {

  use DependencySerializationTrait;

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
   * Constructs a new InvoiceController object.
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
   * Generate an invoice an redirect to the entity print download route.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   */
  public function generate(RouteMatchInterface $route_match, Request $request) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $route_match->getParameter('commerce_order');
    /** @var \Drupal\commerce_invoice\Entity\ $invoice_storage */
    $invoice_storage = $this->entityTypeManager->getStorage('commerce_invoice');
    // Check if the given order is already referenced by an existing invoice.
    $invoice_ids = $invoice_storage->getQuery()
      ->condition('orders', [$order->id()], 'IN')
      ->accessCheck(FALSE)
      ->execute();
    if ($invoice_ids) {
      $invoice_id = reset($invoice_ids);
    }
    else {
      $invoice = $this->invoiceGenerator->generate([$order], $order->getStore(), $order->getBillingProfile(), ['uid' => $order->getCustomerId()]);
      $invoice_id = $invoice->id();
    }
    $url = Url::fromRoute('entity_print.view', [
      'export_type' => 'pdf',
      'entity_id' => $invoice_id,
      'entity_type' => 'commerce_invoice',
    ]);
    // We have to remove the destination, otherwise the redirect doesn't happen.
    if ($request->query->has('destination')) {
      $request->query->remove('destination');
    }
    return new RedirectResponse($url->toString());
  }

  /**
   * Checks access for the invoice generate page.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function checkAccess(RouteMatchInterface $route_match, AccountInterface $account) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $route_match->getParameter('commerce_order');
    if ($order->getState()->getId() == 'canceled' ||
      $order->hasField('cart') && $order->get('cart')->value) {
      return AccessResult::forbidden()->addCacheableDependency($order);
    }

    // The invoice generator service needs a store and a billing profile.
    $order_requirements = !empty($order->getStoreId()) && !empty($order->getBillingProfile());
    $access = AccessResult::allowedIf($order_requirements)
      ->andIf(AccessResult::allowedIfHasPermission($account, 'administer commerce_invoice'))
      ->addCacheableDependency($order);

    return $access;
  }

}
