<?php

namespace Drupal\Tests\commerce_invoice\Functional;

use Drupal\commerce_invoice\Entity\InvoiceType;

/**
 * Tests the invoice type UI.
 *
 * @group commerce_invoice
 */
class InvoiceTypeTest extends InvoiceBrowserTestBase {

  /**
   * Tests whether the default order type was created.
   */
  public function testDefault() {
    $invoice_type = InvoiceType::load('default');
    $this->assertNotEmpty($invoice_type);

    $this->drupalGet('admin/commerce/config/invoice-types');
    $rows = $this->getSession()->getPage()->findAll('css', 'table tbody tr');
    $this->assertCount(1, $rows);
  }

  /**
   * Tests adding an invoice type.
   */
  public function testAdd() {
    InvoiceType::load('default')->delete();

    $this->drupalGet('admin/commerce/config/invoice-types/add');
    $edit = [
      'id' => 'foo',
      'label' => 'Foo',
    ];
    $this->submitForm($edit, t('Save'));
    $this->assertSession()->pageTextContains('Saved the Foo invoice type.');

    $invoice_type = InvoiceType::load('foo');
    $this->assertNotEmpty($invoice_type);
  }

  /**
   * Tests editing an invoice type.
   */
  public function testEdit() {
    $this->drupalGet('admin/commerce/config/invoice-types/default/edit');
    $edit = [
      'label' => 'Default!',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('Saved the Default! invoice type.');

    $invoice_type = InvoiceType::load('default');
    $this->assertNotEmpty($invoice_type);
    $this->assertEquals($edit['label'], $invoice_type->label());
  }

  /**
   * Tests deleting an invoice type.
   */
  public function testDelete() {
    /** @var \Drupal\commerce_invoice\Entity\InvoiceTypeInterface $invoice_type */
    $invoice_type = $this->createEntity('commerce_invoice_type', [
      'id' => 'foo',
      'label' => 'Label for foo',
      'workflow' => 'invoice_default',
    ]);
    $this->drupalGet($invoice_type->toUrl('delete-form'));
    $this->assertSession()->pageTextContains(t('Are you sure you want to delete the invoice type @label?', ['@label' => $invoice_type->label()]));
    $this->assertSession()->pageTextContains(t('This action cannot be undone.'));
    $this->submitForm([], t('Delete'));
    $invoice_type_exists = (bool) InvoiceType::load($invoice_type->id());
    $this->assertEmpty($invoice_type_exists);
  }

  /**
   * Tests invoice type dependencies.
   */
  public function testInvoiceTypeDependencies() {
    $this->drupalGet('admin/commerce/config/invoice-types/default/edit');
    $this->submitForm(['workflow' => 'invoice_test_workflow'], t('Save'));

    $invoice_type = InvoiceType::load('default');
    $this->assertEquals('invoice_test_workflow', $invoice_type->getWorkflowId());
    $dependencies = $invoice_type->getDependencies();
    $this->assertArrayHasKey('module', $dependencies);
    $this->assertContains('commerce_invoice_test', $dependencies['module']);
  }

}
