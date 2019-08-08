<?php

namespace Drupal\Tests\commerce_invoice\Kernel\Entity;

use Drupal\commerce_number_pattern\Entity\NumberPatternInterface;
use Drupal\file\Entity\File;
use Drupal\Tests\commerce_invoice\Kernel\InvoiceKernelTestBase;

/**
 * Tests the invoice type entity.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\Entity\InvoiceType
 *
 * @group commerce_invoice
 */
class InvoiceTypeTest extends InvoiceKernelTestBase {

  /**
   * A test file.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $file;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['file'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('file');
    $file = File::create([
      'fid' => 1,
      'filename' => 'test.png',
      'filesize' => 100,
      'uri' => 'public://images/test.png',
      'filemime' => 'image/png',
    ]);
    $file->save();
    $this->file = $this->reloadEntity($file);
  }

  /**
   * @covers ::id
   * @covers ::label
   * @covers ::getNumberPattern
   * @covers ::getNumberPatternId
   * @covers ::setNumberPatternId
   * @covers ::getLogoUrl
   * @covers ::getLogoFile
   * @covers ::setLogo
   * @covers ::getFooterText
   * @covers ::setFooterText
   * @covers ::getPaymentTerms
   * @covers ::setPaymentTerms
   */
  public function testInvoiceType() {
    $values = [
      'footerText' => $this->randomString(),
      'paymentTerms' => $this->randomString(),
      'numberPattern' => 'invoice_infinite',
      'logo' => $this->file->uuid(),
    ];
    $invoice_type = $this->createInvoiceType('test_id', 'Test label', $values);
    $this->assertEquals('test_id', $invoice_type->id());
    $this->assertEquals('Test label', $invoice_type->label());

    $this->assertEquals($values['numberPattern'], $invoice_type->getNumberPatternId());
    $this->assertInstanceOf(NumberPatternInterface::class, $invoice_type->getNumberPattern());
    $invoice_type->setNumberPatternId('test');
    $this->assertEquals('test', $invoice_type->getNumberPatternId());

    $this->assertEquals($this->file->createFileUrl(FALSE), $invoice_type->getLogoUrl());
    $this->assertEquals($this->file, $invoice_type->getLogoFile());

    $this->assertEquals($values['footerText'], $invoice_type->getFooterText());
    $invoice_type->setFooterText('Footer text (modified)');
    $this->assertEquals('Footer text (modified)', $invoice_type->getFooterText());

    $this->assertEquals($values['paymentTerms'], $invoice_type->getPaymentTerms());
    $invoice_type->setPaymentTerms('Payment terms (modified)');
    $this->assertEquals('Payment terms (modified)', $invoice_type->getPaymentTerms());
  }

}
