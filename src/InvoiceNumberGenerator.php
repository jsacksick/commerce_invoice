<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\Entity\InvoiceType;
use Drupal\commerce_invoice\Entity\InvoiceTypeInterface;
use Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface;
use Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\SupportsInitialNumberOverrideInterface;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Lock\LockBackendInterface;

/**
 * Provides a service for generating invoice numbers.
 */
class InvoiceNumberGenerator implements InvoiceNumberGeneratorInterface {

  /**
   * The database connection to use.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs an InvoiceNumberGenerator object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection to use.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(Connection $connection, LockBackendInterface $lock, TimeInterface $time) {
    $this->connection = $connection;
    $this->lock = $lock;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function generateInvoiceNumber(InvoiceInterface $invoice) {
    $invoice_type = InvoiceType::load($invoice->bundle());
    $store = $invoice->getStore();
    /** @var \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator */
    $number_generator = $invoice_type->getNumberGenerator();
    $next_sequence = $this->getNextSequence($store, $invoice_type);
    return $number_generator->generate($invoice, $next_sequence);
  }

  /**
   * {@inheritdoc}
   */
  public function getNextSequence(StoreInterface $store, InvoiceTypeInterface $invoice_type, $update = TRUE) {
    $invoice_type_id = $invoice_type->id();
    /** @var \Drupal\commerce_invoice\Plugin\Commerce\NumberGenerator\NumberGeneratorInterface $number_generator */
    $number_generator = $invoice_type->getNumberGenerator();
    $lock_name = "commerce_invoice.number_generator.{$store->id()}.{$invoice_type_id}";
    while (!$this->lock->acquire($lock_name)) {
      $this->lock->wait($lock_name);
    }
    // Check if we already have a sequential number in the DB.
    $last_sequence = $this->getLastSequence($store, $invoice_type);
    // Check if the current sequence should be reset.
    if ($last_sequence && !$number_generator->shouldReset($last_sequence)) {
      $next_sequence = $last_sequence->getSequence() + 1;
    }
    else {
      // Determine the initial sequential number to use.
      $next_sequence = 1;
      if ($number_generator instanceof SupportsInitialNumberOverrideInterface) {
        $next_sequence = $number_generator->getInitialNumber();
      }
    }
    $generated = $this->time->getCurrentTime();
    if ($update) {
      $this->connection->merge('commerce_invoice_number_sequence')
        ->fields([
          'store_id' => $store->id(),
          'invoice_type' => $invoice_type_id,
          'sequence' => $next_sequence,
          'generated' => $generated,
        ])
        ->keys([
          'store_id' => $store->id(),
          'invoice_type' => $invoice_type_id
        ])
        ->execute();
    }
    $this->lock->release($lock_name);
    return new InvoiceNumberSequence([
      'store_id' => $store->id(),
      'invoice_type' => $invoice_type_id,
      'sequence' => $next_sequence,
      'generated' => $generated,
    ]);
  }

  /**
   * Gets the last invoice number sequence for the given store/invoice type.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store.
   * @param \Drupal\commerce_invoice\Entity\InvoiceTypeInterface $invoice_type
   *   The invoice type.
   *
   * @return \Drupal\commerce_invoice\InvoiceNumberSequence|null
   *   The current invoice number sequence, or NULL if the sequence hasn't
   *   started yet.
   */
  protected function getLastSequence(StoreInterface $store, InvoiceTypeInterface $invoice_type) {
    $query = $this->connection->select('commerce_invoice_number_sequence', 'cin');
    $query->fields('cin', ['sequence', 'generated']);
    $query
      ->condition('store_id', $store->id())
      ->condition('invoice_type', $invoice_type->id());
    $sequence = $query->execute()->fetchAssoc();

    if (!$sequence) {
      return NULL;
    }

    return new InvoiceNumberSequence([
      'store_id' => $store->id(),
      'invoice_type' => $invoice_type->id(),
      'sequence' => $sequence['sequence'],
      'generated' => $sequence['generated'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function resetSequence(StoreInterface $store, InvoiceTypeInterface $invoice_type) {
    $this->connection->delete('commerce_invoice_number_sequence')
      ->condition('store_id', $store->id())
      ->condition('invoice_type', $invoice_type->id())
      ->execute();
  }

}
