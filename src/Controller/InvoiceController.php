<?php

namespace Drupal\commerce_invoice\Controller;

use Drupal\commerce_invoice\InvoiceGeneratorInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the invoice generate page.
 */
class InvoiceController implements ContainerInjectionInterface {

  use StringTranslationTrait;
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
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new InvoiceController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_invoice\InvoiceGeneratorInterface $invoice_generator
   *   The invoice generator.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, InvoiceGeneratorInterface $invoice_generator, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->invoiceGenerator = $invoice_generator;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('commerce_invoice.invoice_generator'),
      $container->get('messenger')
    );
  }

  /**
   * Generate an invoice and redirect the order invoices tab.
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
    $invoice = $this->invoiceGenerator->generate([$order], $order->getStore(), $order->getBillingProfile(), ['uid' => $order->getCustomerId()]);
    // We can't use the invoice link template here since the same invoice could
    // potentially reference multiple orders.
    $redirect_url = Url::fromRoute('entity.commerce_invoice.order_collection', [
      'commerce_order' => $order->id(),
    ]);
    if ($invoice) {
      $this->messenger->addMessage($this->t('Invoice %label successfully generated.', ['%label' => $invoice->label()]));
    }
    return new RedirectResponse($redirect_url->toString());
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
    if (in_array($order->getState()->getId(), ['canceled', 'draft'])) {
      return AccessResult::forbidden()->mergeCacheMaxAge(0);
    }
    /** @var \Drupal\commerce_invoice\Entity\ $invoice_storage */
    $invoice_storage = $this->entityTypeManager->getStorage('commerce_invoice');
    $invoice_ids = $invoice_storage->getQuery()
      ->condition('orders', [$order->id()], 'IN')
      ->accessCheck(FALSE)
      ->execute();

    // If an invoice already references this order, forbid access.
    if ($invoice_ids) {
      return AccessResult::forbidden()->mergeCacheMaxAge(0);
    }

    // The invoice generator service needs a store and a billing profile.
    $order_requirements = !empty($order->getStoreId()) && !empty($order->getBillingProfile());
    $access = AccessResult::allowedIf($order_requirements)
      ->andIf(AccessResult::allowedIfHasPermission($account, 'administer commerce_invoice'))
      ->mergeCacheMaxAge(0);

    return $access;
  }

}
