<?php

namespace Drupal\matrics_course_booking\Plugin\Archiver;

use Drupal\Core\Archiver\Zip as BaseZip;
use Drupal\Core\Archiver\ArchiverException;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines an archiver implementation for .zip files.
 *
 * @Archiver(
 *   id = "media_download_all_files_zip_archiver",
 *   title = @Translation("Media download all files zip archiver"),
 *   description = @Translation("Handles zip files for media download all files."),
 *   extensions = {"zip"}
 * )
 */
class Zip extends BaseZip {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct($file_path) {
    $this->zip = new \ZipArchive();
    if ((file_exists($file_path) && $this->zip->open($file_path, \ZipArchive::OVERWRITE) !== TRUE) || $this->zip->open($file_path, \ZipArchive::CREATE) !== TRUE) {
      throw new ArchiverException($this->t('Cannot open %file_path', ['%file_path' => $file_path]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function add($file_path) {
    $local_name = basename($file_path);
    $this->zip->addFile($file_path, $local_name);

    return $this;
  }

  /**
   * Method to close the opened archive file.
   */
  public function close() {
    $this->zip->close();
  }

}
