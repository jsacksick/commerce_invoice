<?php

namespace Drupal\Tests\commerce_invoice\Functional;

use Drupal\commerce_invoice\Entity\InvoiceItemType;

/**
 * Tests the invoice item type UI.
 *
 * @group commerce_invoice
 */
class InvoiceItemTypeTest extends InvoiceBrowserTestBase {

  /**
   * Tests whether the default invoice item type was created.
   */
  public function testDefault() {
    $invoice_type = InvoiceItemType::load('default');
    $this->assertNotEmpty($invoice_type);

    $this->drupalGet('admin/commerce/config/invoice-item-types');
    $rows = $this->getSession()->getPage()->findAll('css', 'table tbody tr');
    $this->assertCount(1, $rows);
  }

  /**
   * Tests adding an invoice item type.
   */
  public function testAdd() {
    InvoiceItemType::load('default')->delete();

    $this->drupalGet('admin/commerce/config/invoice-item-types/add');
    $edit = [
      'id' => 'foo',
      'label' => 'Foo',
    ];
    $this->submitForm($edit, t('Save'));
    $this->assertSession()->pageTextContains('Saved the Foo invoice item type.');

    $invoice_item_type = InvoiceItemType::load('foo');
    $this->assertNotEmpty($invoice_item_type);
  }

  /**
   * Tests editing an invoice item type.
   */
  public function testEdit() {
    $this->drupalGet('admin/commerce/config/invoice-item-types/default/edit');
    $edit = [
      'label' => 'Default!',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('Saved the Default! invoice item type.');

    $invoice_item_type = InvoiceItemType::load('default');
    $this->assertNotEmpty($invoice_item_type);
    $this->assertEquals($edit['label'], $invoice_item_type->label());
  }

  /**
   * Tests deleting an invoice item type.
   */
  public function testDelete() {
    /** @var \Drupal\commerce_invoice\Entity\InvoiceItemTypeInterface $invoice_item_type */
    $invoice_item_type = $this->createEntity('commerce_invoice_item_type', [
      'id' => 'foo',
      'label' => 'Label for foo',
    ]);
    $this->drupalGet($invoice_item_type->toUrl('delete-form'));
    $this->assertSession()->pageTextContains(t('Are you sure you want to delete the invoice item type @label?', ['@label' => $invoice_item_type->label()]));
    $this->assertSession()->pageTextContains(t('This action cannot be undone.'));
    $this->submitForm([], t('Delete'));
    $invoice_item_type_exists = (bool) InvoiceItemType::load($invoice_item_type->id());
    $this->assertEmpty($invoice_item_type_exists);
  }

}
