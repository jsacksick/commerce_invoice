<?php

namespace Drupal\Tests\commerce_invoice\Functional;

use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_price\Price;
use Drupal\Core\Url;
use Drupal\views\Entity\View;

/**
 * Tests the invoice admin UI.
 *
 * @group commerce_invoice
 */
class InvoiceAdminTest extends InvoiceBrowserTestBase {

  /**
   * The invoice generate uri.
   *
   * @var string
   */
  protected $invoiceGenerateUri;

  /**
   * A sample order.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $order;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_cart',
    'commerce_product',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $order_type = OrderType::load('default');
    $order_type->setThirdPartySetting('commerce_invoice', 'invoice_type', 'default');
    $order_type->save();

    $variation = $this->createEntity('commerce_product_variation', [
      'title' => $this->randomMachineName(),
      'type' => 'default',
      'sku' => 'sku-' . $this->randomMachineName(),
      'price' => [
        'number' => '7.99',
        'currency_code' => 'USD',
      ],
    ]);
    $order_item = $this->createEntity('commerce_order_item', [
      'title' => $this->randomMachineName(),
      'type' => 'default',
      'quantity' => 1,
      'unit_price' => new Price('10', 'USD'),
      'purchased_entity' => $variation,
    ]);
    $order_item->save();
    $this->order = $this->createEntity('commerce_order', [
      'uid' => $this->loggedInUser->id(),
      'order_number' => '6',
      'type' => 'default',
      'state' => 'completed',
      'order_items' => [$order_item],
      'store_id' => $this->store,
    ]);
    $this->invoiceGenerateUri = Url::fromRoute('entity.commerce_invoice.generate', [
      'commerce_order' => $this->order->id(),
    ])->toString();
  }

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_order',
      'access commerce_order overview',
    ], parent::getAdministratorPermissions());
  }

  /**
   * Tests the "Download invoice" operation visibility.
   */
  public function testDownloadInvoiceOperation() {
    // Ensure the "Download invoice" operation is not shown for a draft order.
    $this->drupalGet($this->order->toUrl('collection'));
    $this->assertSession()->linkNotExists($this->invoiceGenerateUri);
    $order_edit_link = $this->order->toUrl('edit-form')->toString();
    $this->assertSession()->linkByHrefExists($order_edit_link);

    $this->order->set('state', 'completed');
    $this->order->save();
    $this->drupalGet($this->order->toUrl('collection'));
    $this->assertSession()->linkByHrefExists($this->invoiceGenerateUri);
  }

  /**
   * Tests the Invoices listing with and without the view.
   */
  public function testInvoiceListing() {
    $invoice_collection_route = Url::fromRoute('entity.commerce_invoice.collection');
    $this->drupalGet($invoice_collection_route);
    $this->assertSession()->pageTextContains('There are no invoices yet.');
    $invoice = $this->createEntity('commerce_invoice', [
      'type' => 'default',
      'invoice_number' => $this->randomString(),
      'store_id' => $this->store->id(),
      'orders' => [$this->order->id()],
      'total_price' => new Price('10', 'USD'),
    ]);
    $this->getSession()->reload();
    $this->assertSession()->pageTextNotContains('There are no invoices yet.');
    $this->assertSession()->pageTextContains($invoice->label());
    $this->assertSession()->pageTextContains('Download');

    // Ensure the listing works without the view.
    View::load('commerce_invoices')->delete();
    \Drupal::service('router.builder')->rebuild();
    $this->drupalGet($invoice_collection_route);
    $this->assertSession()->pageTextNotContains('There are no invoices yet.');
    $this->assertSession()->pageTextContains($invoice->label());
    $invoice->delete();
    $this->getSession()->reload();
    $this->assertSession()->pageTextContains('There are no invoices yet.');
  }

}
