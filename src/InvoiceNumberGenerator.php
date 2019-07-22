<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\commerce_invoice\Entity\InvoiceType;
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
    $plugin_id = $number_generator->getPluginId();
    $lock_name = "commerce_invoice.number_generator.{$store->id()}.{$plugin_id}";
    while (!$this->lock->acquire($lock_name)) {
      $this->lock->wait($lock_name);
    }
    $last_sequence = $this->getLastSequence($store, $plugin_id);
    // Check if the current sequence should be reset.
    // @todo: Expand comments.
    if ($last_sequence && $number_generator->shouldReset($last_sequence)) {
      $last_sequence = NULL;
    }
    $next_sequence = $this->getNextSequence($store, $plugin_id, $last_sequence);
    $this->lock->release($lock_name);
    return $number_generator->generate($invoice, $next_sequence);
  }

  /**
   * Gets the next invoice number sequence.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store.
   * @param $plugin_id
   *   The number generator plugin ID.
   * @param \Drupal\commerce_invoice\InvoiceNumberSequence|null $current_sequence
   *   The current invoice number sequence, or NULL if the sequence hasn't
   *   started yet.
   *
   * @return \Drupal\commerce_invoice\InvoiceNumberSequence
   *   The next invoice number sequence.
   */
  protected function getNextSequence(StoreInterface $store, $plugin_id, InvoiceNumberSequence $current_sequence = NULL) {
    $next_sequence = 1;
    if ($current_sequence) {
      $next_sequence = $current_sequence->getSequence() + 1;
    }
    $generated = $this->time->getCurrentTime();
    $this->connection->merge('commerce_invoice_number_sequence')
      ->fields([
        'store_id' => $store->id(),
        'plugin_id' => $plugin_id,
        'sequence' => $next_sequence,
        'generated' => $generated,
      ])
      ->keys([
        'store_id' => $store->id(),
        'plugin_id' => $plugin_id
      ])
      ->execute();

    return new InvoiceNumberSequence([
      'store_id' => $store->id(),
      'plugin_id' => $plugin_id,
      'sequence' => $next_sequence,
      'generated' => $generated,
    ]);
  }

  /**
   * Gets the last invoice number sequence for the given store and plugin.
   *
   * @param \Drupal\commerce_store\Entity\StoreInterface $store
   *   The store.
   * @param $plugin_id
   *   The number generator plugin ID.
   *
   * @return \Drupal\commerce_invoice\InvoiceNumberSequence|null
   *   The current invoice number sequence, or NULL if the sequence hasn't
   *   started yet.
   */
  protected function getLastSequence(StoreInterface $store, $plugin_id) {
    $query = $this->connection->select('commerce_invoice_number_sequence', 'cin');
    $query->fields('cin', ['sequence', 'generated']);
    $query
      ->condition('store_id', $store->id())
      ->condition('plugin_id', $plugin_id);
    $sequence = $query->execute()->fetchAssoc();

    if (!$sequence) {
      return NULL;
    }

    return new InvoiceNumberSequence([
      'store_id' => $store->id(),
      'plugin_id' => $plugin_id,
      'sequence' => $sequence['sequence'],
      'generated' => $sequence['generated'],
    ]);
  }

}
