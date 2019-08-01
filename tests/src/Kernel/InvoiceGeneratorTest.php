<?php

namespace Drupal\Tests\commerce_invoice\Kernel;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_order\Entity\OrderItemType;
use Drupal\commerce_price\Price;
use Drupal\profile\Entity\Profile;

/**
 * Tests the invoice generator service.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\InvoiceGenerator
 * @group commerce_invoice
 */
class InvoiceGeneratorTest extends InvoiceKernelTestBase {

  /**
   * The invoice generator service.
   *
   * @var \Drupal\commerce_invoice\InvoiceGeneratorInterface
   */
  protected $invoiceGenerator;

  /**
   * A sample user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    OrderItemType::create([
      'id' => 'test',
      'label' => 'Test',
      'orderType' => 'default',
    ])->save();
    $this->invoiceGenerator = $this->container->get('commerce_invoice.invoice_generator');
    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
  }

  /**
   * Tests generating invoices.
   *
   * @covers ::generate
   */
  public function testGenerate() {
    $order_item = OrderItem::create([
      'type' => 'test',
      'quantity' => 1,
      'unit_price' => new Price('12.00', 'USD'),
    ]);
    $order_item->save();
    $profile = Profile::create([
      'type' => 'customer',
      'address' => [
        'country_code' => 'US',
        'postal_code' => '53177',
        'locality' => 'Milwaukee',
        'address_line1' => 'Pabst Blue Ribbon Dr',
        'administrative_area' => 'WI',
        'given_name' => 'Frederick',
        'family_name' => 'Pabst',
      ],
      'uid' => $this->user->id(),
    ]);
    $profile->save();
    /** @var \Drupal\profile\Entity\ProfileInterface $profile */
    $profile = $this->reloadEntity($profile);
    $order = Order::create([
      'type' => 'default',
      'state' => 'completed',
      'store_id' => $this->store,
      'billing_profile' => $profile,
      'uid' => $this->user->id(),
      'order_items' => [$order_item],
      'adjustments' => [
        new Adjustment([
          'type' => 'custom',
          'label' => '10% off',
          'amount' => new Price('-1.20', 'USD'),
          'percentage' => '0.1',
        ]),
      ],
      'total_paid' => new Price('5.00', 'USD'),
    ]);
    $order->save();
    $order = $this->reloadEntity($order);
    $another_order_item = OrderItem::create([
      'type' => 'test',
      'quantity' => 3,
      'unit_price' => new Price('10.00', 'USD'),
    ]);
    $another_order_item->save();
    $another_order = Order::create([
      'type' => 'default',
      'state' => 'completed',
      'store_id' => $this->store,
      'uid' => $this->user->id(),
      'billing_profile' => $profile,
      'order_items' => [$another_order_item],
      'adjustments' => [
        new Adjustment([
          'type' => 'fee',
          'label' => 'Random fee',
          'amount' => new Price('2.00', 'USD'),
        ]),
      ],
    ]);
    $another_order->save();
    $another_order = $this->reloadEntity($another_order);
    $invoice = $this->invoiceGenerator->generate([$order, $another_order], $this->store, $profile, ['uid' => $this->user->id()]);
    $invoice_billing_profile = $invoice->getBillingProfile();
    $this->assertNotEmpty($invoice->getBillingProfile());
    $this->assertTrue($profile->equalToProfile($invoice_billing_profile));
    $this->assertEquals($this->user->id(), $invoice->getCustomerId());

    $this->assertEquals([$order, $another_order], $invoice->getOrders());
    $this->assertEquals($this->store, $invoice->getStore());
    $this->assertEquals(new Price('42.8', 'USD'), $invoice->getTotalPrice());
    $this->assertCount(2, $invoice->getItems());
    $this->assertCount(2, $invoice->getAdjustments());
    $this->assertEquals(new Price('5.00', 'USD'), $invoice->getTotalPaid());
  }

}
