<?php

namespace Drupal\matrics_course_booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\matrics_course_booking\Plugin\Archiver\Zip;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * {@inheritdoc}
 */
class DownloadAllFiles extends ControllerBase {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('file_system'),
      $container->get('messenger'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(FileSystemInterface $file_system, Messenger $messenger, EntityTypeManagerInterface $entityTypeManager) {
    $this->fileSystem = $file_system;
    $this->messenger = $messenger;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function downloadAllFiles($id) {
    $p = Paragraph::load($id);
    $documents = $p->field_documents->getValue();
    $zip_files_directory = DRUPAL_ROOT . '/sites/default/files/daf_zips';
    $file_path = $zip_files_directory . '/course-documents-' . $id . '.zip';
    $redirect_on_error_to = empty($_SERVER['HTTP_REFERER']) ? '/' : $_SERVER['HTTP_REFERER'];
    $files = [];
    foreach ($documents as $document) {
      $file_obj = $this->entityTypeManager->getStorage('file')->load($document['target_id']);
      if ($file_obj) {
        $files[] = $file_obj->getFileUri();
      }
    }
    $file_zip = NULL;
    if ($this->fileSystem->prepareDirectory($zip_files_directory, FileSystemInterface::CREATE_DIRECTORY)) {
      foreach ($files as $file) {
        $file = $this->fileSystem->realpath($file);
        if (!$file_zip instanceof Zip) {

          $file_zip = new Zip($file_path);
        }
        if ($file) {
          $file_zip->add($file);
        }
      }

      if ($file_zip instanceof Zip) {
        $file_zip->close();
        return $this->streamZipFile($file_path);
      }
      else {
        $this->messenger->addMessage('No files found for this node to be downloaded', 'error', TRUE);
        return new RedirectResponse($redirect_on_error_to);
      }
    }
    else {
      $this->messenger->addMessage('Zip file directory not found.', 'error', TRUE);
      return new RedirectResponse($redirect_on_error_to);
    }

    return $id;
  }

  /**
   * Method to stream created zip file.
   *
   * @param string $file_path
   *   File physical path.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   *   Downloads the file.
   */
  protected function streamZipFile($file_path) {
    $binary_file_response = new BinaryFileResponse($file_path);
    $binary_file_response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($file_path));

    return $binary_file_response;
  }

}
