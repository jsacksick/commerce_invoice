<?php

namespace Drupal\Tests\commerce_invoice\Kernel\Entity;

use Drupal\commerce_invoice\Entity\Invoice;
use Drupal\commerce_order\Adjustment;
use Drupal\commerce_invoice\Entity\InvoiceItem;
use Drupal\commerce_price\Price;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\profile\Entity\Profile;
use Drupal\Tests\commerce_invoice\Kernel\InvoiceKernelTestBase;
use Drupal\user\UserInterface;

/**
 * Tests the invoice entity.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\Entity\Invoice
 *
 * @group commerce_invoice
 */
class InvoiceTest extends InvoiceKernelTestBase {

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

    $user = $this->createUser();
    $this->user = $this->reloadEntity($user);
  }

  /**
   * Tests the invoice entity and its methods.
   *
   * @covers ::getInvoiceNumber
   * @covers ::setInvoiceNumber
   * @covers ::getStore
   * @covers ::setStore
   * @covers ::getStoreId
   * @covers ::setStoreId
   * @covers ::getCustomer
   * @covers ::setCustomer
   * @covers ::getCustomerId
   * @covers ::setCustomerId
   * @covers ::getBillingProfile
   * @covers ::setBillingProfile
   * @covers ::getItems
   * @covers ::setItems
   * @covers ::hasItems
   * @covers ::addItem
   * @covers ::removeItem
   * @covers ::hasItem
   * @covers ::getAdjustments
   * @covers ::setAdjustments
   * @covers ::addAdjustment
   * @covers ::removeAdjustment
   * @covers ::collectAdjustments
   * @covers ::recalculateTotalPrice
   * @covers ::getTotalPrice
   * @covers ::getState
   * @covers ::getCreatedTime
   * @covers ::setCreatedTime
   * @covers ::getChangedTime
   * @covers ::setChangedTime
   * @covers ::getDueDate
   * @covers ::setDueDate
   */
  public function testInvoice() {
    /** @var \Drupal\profile\Entity\ProfileInterface $profile */
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
    ]);
    $profile->save();
    $profile = $this->reloadEntity($profile);

    /** @var \Drupal\commerce_invoice\Entity\InvoiceItemInterface $invoice_item */
    $invoice_item = InvoiceItem::create([
      'type' => 'commerce_product_variation',
      'quantity' => '1',
      'unit_price' => new Price('2.00', 'USD'),
    ]);
    $invoice_item->save();
    $invoice_item = $this->reloadEntity($invoice_item);
    /** @var \Drupal\commerce_invoice\Entity\InvoiceItemInterface $another_invoice_item */
    $another_invoice_item = InvoiceItem::create([
      'type' => 'commerce_product_variation',
      'quantity' => '2',
      'unit_price' => new Price('3.00', 'USD'),
    ]);
    $another_invoice_item->save();
    $another_invoice_item = $this->reloadEntity($another_invoice_item);

    /** @var \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice */
    $invoice = Invoice::create([
      'type' => 'default',
    ]);
    $invoice->save();

    $invoice->setInvoiceNumber(7);
    $this->assertEquals(7, $invoice->getInvoiceNumber());

    $invoice->setStore($this->store);
    $this->assertEquals($this->store, $invoice->getStore());
    $this->assertEquals($this->store->id(), $invoice->getStoreId());
    $invoice->setStoreId(0);
    $this->assertEquals(NULL, $invoice->getStore());
    $invoice->setStoreId($this->store->id());
    $this->assertEquals($this->store, $invoice->getStore());
    $this->assertEquals($this->store->id(), $invoice->getStoreId());

    $this->assertInstanceOf(UserInterface::class, $invoice->getCustomer());
    $this->assertTrue($invoice->getCustomer()->isAnonymous());
    $this->assertEquals(0, $invoice->getCustomerId());
    $invoice->setCustomer($this->user);
    $this->assertEquals($this->user, $invoice->getCustomer());
    $this->assertEquals($this->user->id(), $invoice->getCustomerId());
    $this->assertTrue($invoice->getCustomer()->isAuthenticated());
    // Non-existent/deleted user ID.
    $invoice->setCustomerId(888);
    $this->assertInstanceOf(UserInterface::class, $invoice->getCustomer());
    $this->assertTrue($invoice->getCustomer()->isAnonymous());
    $this->assertEquals(888, $invoice->getCustomerId());
    $invoice->setCustomerId($this->user->id());
    $this->assertEquals($this->user, $invoice->getCustomer());
    $this->assertEquals($this->user->id(), $invoice->getCustomerId());

    $invoice->setBillingProfile($profile);
    $this->assertEquals($profile, $invoice->getBillingProfile());

    $invoice->setItems([$invoice_item, $another_invoice_item]);
    $this->assertEquals([$invoice_item, $another_invoice_item], $invoice->getItems());
    $this->assertNotEmpty($invoice->hasItems());
    $invoice->removeItem($another_invoice_item);
    $this->assertEquals([$invoice_item], $invoice->getItems());
    $this->assertNotEmpty($invoice->hasItem($invoice_item));
    $this->assertEmpty($invoice->hasItem($another_invoice_item));
    $invoice->addItem($another_invoice_item);
    $this->assertEquals([$invoice_item, $another_invoice_item], $invoice->getItems());
    $this->assertNotEmpty($invoice->hasItem($another_invoice_item));
    $this->assertEquals(new Price('8.00', 'USD'), $invoice->getTotalPrice());

    $adjustments = [];
    $adjustments[] = new Adjustment([
      'type' => 'custom',
      'label' => '10% off',
      'amount' => new Price('-1.00', 'USD'),
    ]);
    $adjustments[] = new Adjustment([
      'type' => 'fee',
      'label' => 'Handling fee',
      'amount' => new Price('10.00', 'USD'),
      'locked' => TRUE,
    ]);
    $invoice->addAdjustment($adjustments[0]);
    $invoice->addAdjustment($adjustments[1]);
    $this->assertEquals($adjustments, $invoice->getAdjustments());
    $this->assertEquals($adjustments, $invoice->getAdjustments(['custom', 'fee']));
    $this->assertEquals([$adjustments[0]], $invoice->getAdjustments(['custom']));
    $this->assertEquals([$adjustments[1]], $invoice->getAdjustments(['fee']));
    $invoice->removeAdjustment($adjustments[0]);
    $this->assertEquals(new Price('8.00', 'USD'), $invoice->getSubtotalPrice());
    $this->assertEquals(new Price('18.00', 'USD'), $invoice->getTotalPrice());
    $this->assertEquals([$adjustments[1]], $invoice->getAdjustments());
    $invoice->setAdjustments($adjustments);
    $this->assertEquals($adjustments, $invoice->getAdjustments());
    $this->assertEquals(new Price('17.00', 'USD'), $invoice->getTotalPrice());

    $this->assertEquals($adjustments, $invoice->collectAdjustments());
    $this->assertEquals($adjustments, $invoice->collectAdjustments(['custom', 'fee']));
    $this->assertEquals([$adjustments[0]], $invoice->collectAdjustments(['custom']));
    $this->assertEquals([$adjustments[1]], $invoice->collectAdjustments(['fee']));

    $this->assertEquals('pending', $invoice->getState()->getId());
    $invoice->setCreatedTime(635879700);
    $this->assertEquals(635879700, $invoice->getCreatedTime());

    $invoice->setChangedTime(635879800);
    $this->assertEquals(635879800, $invoice->getChangedTime());

    $invoice->setDueDate(new DrupalDateTime('2017-01-01'));
    $this->assertEquals('2017-01-01', $invoice->getDueDate()->format('Y-m-d'));
  }

}
