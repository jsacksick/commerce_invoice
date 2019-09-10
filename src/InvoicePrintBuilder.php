<?php

namespace Drupal\commerce_invoice;

use Drupal\commerce_invoice\Entity\InvoiceInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_print\FilenameGeneratorInterface;
use Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface;
use Drupal\entity_print\PrintBuilderInterface;
use Drupal\entity_print\PrintEngineException;

/**
 * The print builder service.
 */
class InvoicePrintBuilder implements InvoicePrintBuilderInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity storage for the 'file' entity type.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The Entity print plugin manager.
   *
   * @var \Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface
   */
  protected $pluginManager;

  /**
   * The Entity print builder.
   *
   * @var \Drupal\entity_print\PrintBuilderInterface
   */
  protected $printBuilder;

  /**
   * The Entity print filename generator.
   *
   * @var \Drupal\entity_print\FilenameGeneratorInterface
   */
  protected $filenameGenerator;

  /**
   * The Current User object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new InvoicePrintBuilder object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface $plugin_manager
   *   The Entity print plugin manager.
   * @param \Drupal\entity_print\PrintBuilderInterface $print_builder
   *   The Entity print builder.
   * @param \Drupal\entity_print\FilenameGeneratorInterface $filename_generator
   *   The Entity print filename generator.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityPrintPluginManagerInterface $plugin_manager, PrintBuilderInterface $print_builder, FilenameGeneratorInterface $filename_generator, AccountInterface $current_user) {
    $this->configFactory = $config_factory;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->pluginManager = $plugin_manager;
    $this->printBuilder = $print_builder;
    $this->filenameGenerator = $filename_generator;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function build(InvoiceInterface $invoice) {
    $filename = $this->generateFilename($invoice);
    $langcode = $invoice->language()->getId();
    $files = $this->fileStorage->loadByProperties([
      'uri' => "private://$filename",
      'langcode' => $langcode,
    ]);

    if ($files) {
      return $this->fileStorage->load(key($files));
    }

    try {
      $print_engine = $this->pluginManager->createSelectedInstance('pdf');
    }
    catch (PrintEngineException $e) {
      watchdog_exception('commerce_invoice', $e);
      return FALSE;
    }
    $config = $this->configFactory->get('entity_print.settings');
    $uri = $this->printBuilder->savePrintable([$invoice], $print_engine, 'private', $filename, $config->get('default_css'));

    if (!$uri) {
      return FALSE;
    }

    $file = $this->fileStorage->create([
      'uri' => $uri,
      'uid' => $this->currentUser->id(),
      'langcode' => $langcode,
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $file->save();
    return $file;
  }

  /**
   * Generates a filename for the given invoice.
   *
   * @param \Drupal\commerce_invoice\Entity\InvoiceInterface $invoice
   *   The invoice.
   *
   * @return string
   *   The generated filename.
   */
  protected function generateFilename(InvoiceInterface $invoice) {
    $file_name = $this->filenameGenerator->generateFilename([$invoice]);
    $file_name .= '-' . $invoice->language()->getId() . '-' . str_replace('_', '', $invoice->getState()->getId()) . '.pdf';
    return $file_name;
  }

}
