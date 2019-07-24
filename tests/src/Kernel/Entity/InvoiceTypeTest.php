<?php

namespace Drupal\Tests\commerce_invoice\Kernel\Entity;

use Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface;
use Drupal\Tests\commerce_invoice\Kernel\InvoiceKernelTestBase;

/**
 * Tests the invoice type entity.
 *
 * @coversDefaultClass \Drupal\commerce_invoice\Entity\InvoiceType
 *
 * @group commerce_recurring
 */
class InvoiceTypeTest extends InvoiceKernelTestBase {

  /**
   * @covers ::id
   * @covers ::label
   * @covers ::getFooterText
   * @covers ::setFooterText
   * @covers ::getPaymentTerms
   * @covers ::setPaymentTerms
   * @covers ::getNumberGeneratorId
   * @covers ::setNumberGeneratorId
   * @covers ::getNumberGenerator
   * @covers ::getNumberGeneratorConfiguration
   * @covers ::setNumberGeneratorConfiguration
   */
  public function testInvoiceType() {
    $values = [
      'footerText' => $this->randomString(),
      'paymentTerms' => $this->randomString(),
      'numberGenerator' => 'monthly',
      'numberGeneratorConfiguration' => [
        'pattern' => '[current-date:custom:Y-m]-{number}',
        'padding' => 2,
      ],
    ];
    $invoice_type = $this->createInvoiceType('test_id', 'Test label', $values);
    $this->assertEquals('test_id', $invoice_type->id());
    $this->assertEquals('Test label', $invoice_type->label());
    $this->assertEquals($values['footerText'], $invoice_type->getFooterText());
    $invoice_type->setFooterText('Footer text (modified)');
    $this->assertEquals('Footer text (modified)', $invoice_type->getFooterText());

    $this->assertEquals($values['paymentTerms'], $invoice_type->getPaymentTerms());
    $invoice_type->setPaymentTerms('Payment terms (modified)');
    $this->assertEquals('Payment terms (modified)', $invoice_type->getPaymentTerms());

    $number_generator = $invoice_type->getNumberGenerator();
    $this->assertInstanceOf(NumberGeneratorInterface::class, $number_generator);
    $this->assertEquals('monthly', $number_generator->getPluginId());
    $this->assertEquals($invoice_type->getNumberGeneratorConfiguration(), $number_generator->getConfiguration());
    $invoice_type->setNumberGeneratorConfiguration([
      'pattern' => 'INV-[current-date:custom:Y-m]-{number}',
      'padding' => 5,
    ]);
    $this->assertEquals([
      'pattern' => 'INV-[current-date:custom:Y-m]-{number}',
      'padding' => 5,
    ], $invoice_type->getNumberGeneratorConfiguration());

    $invoice_type->setNumberGeneratorId('yearly');
    $this->assertEquals('yearly', $invoice_type->getNumberGeneratorId());
    $this->assertEmpty($invoice_type->getNumberGeneratorConfiguration());
  }

}
