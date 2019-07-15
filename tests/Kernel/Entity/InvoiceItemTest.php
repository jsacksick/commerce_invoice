<?php

namespace Drupal\Tests\commerce_invoice\Kernel\Entity;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_invoice\Entity\InvoiceItem;
use Drupal\commerce_price\Price;
use Drupal\Tests\commerce\Kernel\CommerceKernelTestBase;

/**
 * Tests the invoice item entity.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\Entity\InvoiceItem
 *
 * @group commerce
 */
class InvoiceItemTest extends CommerceKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference_revisions',
    'profile',
    'state_machine',
    'commerce_order',
    'commerce_invoice',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('profile');
    $this->installEntitySchema('commerce_invoice');
    $this->installEntitySchema('commerce_invoice_item');
    $this->installConfig('commerce_invoice');
  }

  /**
   * Tests the invoice item entity and its methods.
   *
   * @covers ::getTitle
   * @covers ::setTitle
   * @covers ::getQuantity
   * @covers ::setQuantity
   * @covers ::getUnitPrice
   * @covers ::setUnitPrice
   * @covers ::getTotalPrice
   * @covers ::recalculateTotalPrice
   * @covers ::getAdjustments
   * @covers ::setAdjustments
   * @covers ::addAdjustment
   * @covers ::removeAdjustment
   * @covers ::getAdjustedTotalPrice
   * @covers ::getAdjustedUnitPrice
   * @covers ::getData
   * @covers ::setData
   * @covers ::unsetData
   * @covers ::getCreatedTime
   * @covers ::setCreatedTime
   */
  public function testInvoiceItem() {
    $invoice_item = InvoiceItem::create([
      'type' => 'default',
    ]);
    $invoice_item->save();

    $invoice_item->setTitle('My invoice item');
    $this->assertEquals('My invoice item', $invoice_item->getTitle());

    $this->assertEquals(1, $invoice_item->getQuantity());
    $invoice_item->setQuantity('2');
    $this->assertEquals(2, $invoice_item->getQuantity());

    $this->assertEquals(NULL, $invoice_item->getUnitPrice());
    $unit_price = new Price('9.99', 'USD');
    $invoice_item->setUnitPrice($unit_price);
    $this->assertEquals($unit_price, $invoice_item->getUnitPrice());

    $adjustments = [];
    $adjustments[] = new Adjustment([
      'type' => 'custom',
      'label' => '10% off',
      'amount' => new Price('-1.00', 'USD'),
      'percentage' => '0.1',
    ]);
    $adjustments[] = new Adjustment([
      'type' => 'fee',
      'label' => 'Random fee',
      'amount' => new Price('2.00', 'USD'),
    ]);
    $invoice_item->addAdjustment($adjustments[0]);
    $invoice_item->addAdjustment($adjustments[1]);
    $adjustments = $invoice_item->getAdjustments();
    $this->assertEquals($adjustments, $invoice_item->getAdjustments());
    $this->assertEquals($adjustments, $invoice_item->getAdjustments(['custom', 'fee']));
    $this->assertEquals([$adjustments[0]], $invoice_item->getAdjustments(['custom']));
    $this->assertEquals([$adjustments[1]], $invoice_item->getAdjustments(['fee']));
    $invoice_item->removeAdjustment($adjustments[0]);
    $this->assertEquals([$adjustments[1]], $invoice_item->getAdjustments());
    $this->assertEquals(new Price('21.98', 'USD'), $invoice_item->getAdjustedTotalPrice());
    $this->assertEquals(new Price('10.99', 'USD'), $invoice_item->getAdjustedUnitPrice());
    $invoice_item->setAdjustments($adjustments);
    $this->assertEquals($adjustments, $invoice_item->getAdjustments());
    $this->assertEquals(new Price('9.99', 'USD'), $invoice_item->getUnitPrice());
    $this->assertEquals(new Price('19.98', 'USD'), $invoice_item->getTotalPrice());
    $this->assertEquals(new Price('20.98', 'USD'), $invoice_item->getAdjustedTotalPrice());
    $this->assertEquals(new Price('18.98', 'USD'), $invoice_item->getAdjustedTotalPrice(['custom']));
    $this->assertEquals(new Price('21.98', 'USD'), $invoice_item->getAdjustedTotalPrice(['fee']));
    // The adjusted unit prices are the adjusted total prices divided by 2.
    $this->assertEquals(new Price('10.49', 'USD'), $invoice_item->getAdjustedUnitPrice());
    $this->assertEquals(new Price('9.49', 'USD'), $invoice_item->getAdjustedUnitPrice(['custom']));
    $this->assertEquals(new Price('10.99', 'USD'), $invoice_item->getAdjustedUnitPrice(['fee']));

    $this->assertEquals('default', $invoice_item->getData('test', 'default'));
    $invoice_item->setData('test', 'value');
    $this->assertEquals('value', $invoice_item->getData('test', 'default'));
    $invoice_item->unsetData('test');
    $this->assertNull($invoice_item->getData('test'));
    $this->assertEquals('default', $invoice_item->getData('test', 'default'));

    $invoice_item->setCreatedTime(635879700);
    $this->assertEquals(635879700, $invoice_item->getCreatedTime());
  }

}
