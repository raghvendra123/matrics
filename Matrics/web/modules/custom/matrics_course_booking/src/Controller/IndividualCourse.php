<?php

namespace Drupal\matrics_course_booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\file\Entity\File;

/**
 * {@inheritdoc}
 */
class IndividualCourse extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function list() {
    $query = \Drupal::database()->select('node_field_data', 'n')
      ->fields('n', ['nid', 'status', 'type', 'created', 'title'])
      ->condition('n.status', 1)
      ->condition('n.type', 'courses')
      ->orderBy('n.created', 'DESC');
    $results = $query->execute()->fetchAll();
    $element = [
      '#theme' => 'training_management',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function document_list($id) {
    $p = Paragraph::load($id);
    $documents = $p->field_documents->getValue();

    $files = [];
    foreach ($documents as $document) {
      $file = File::load($document['target_id']);
      $uri = $file->getFileUri();
      $files[] = $file->getFileUri();
    }
  }

}
