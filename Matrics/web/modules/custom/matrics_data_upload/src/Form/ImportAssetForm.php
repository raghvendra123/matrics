<?php

namespace Drupal\matrics_data_upload\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Drupal\node\Entity\Node;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class ImportAssetForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asset_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [
      '#attributes' => ['enctype' => 'multipart/form-data'],
    ];

    $validators = [
      'file_validate_extensions' => ['xlsx'],
    ];
    $options = [];
    $user = \Drupal::currentUser()->id();
    $taxonomies = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('customer', 0, NULL, TRUE);
    foreach ($taxonomies as $term) {
      $access_storage = \Drupal::service('permissions_by_term.access_storage');
      $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $aAllowedUsers = $access_storage->getAllowedUserIds($term->id(), $langcode);
      if (in_array($user, $aAllowedUsers)) {
        $options[$term->id()] = $term->getName();
      }
    }
    $form['term'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => 'Customer',
      '#required' => 'required',
    ];
    $form['excel_file'] = [
      '#type' => 'managed_file',
      '#name' => 'excel_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('Excel format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
      '#description' => t('Note: Excel format only. Download Sample File from <a href="../../../modules/custom/matrics_data_upload/sheet/asset.xlsx" download>click here </a>'),
    ];
    $form['#attached']['library'][] = 'matrics_data_upload/message';
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('excel_file') == NULL) {
      $form_state->setErrorByName('excel_file', $this->t('upload proper File'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = \Drupal::entityTypeManager()->getStorage('file')
      ->load($form_state->getValue('excel_file')[0]);
    $full_path = $file->get('uri')->value;
    $file_name = basename($full_path);

    $inputFileName = \Drupal::service('file_system')->realpath('public://' . $file_name);

    $spreadsheet = IOFactory::load($inputFileName);

    $sheetData = $spreadsheet->getActiveSheet();

    $rows = [];
    foreach ($sheetData->getRowIterator() as $row) {
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(FALSE);
      $cells = [];
      foreach ($cellIterator as $cell) {
        if ($cell->getValue() != NULL) {  
          $cells[] = $cell->getValue();
        }
      }
      $rows[] = $cells;
    }
    unset($rows[0]);
    foreach ($rows as $row) {
      if (!empty($row)) {
        if ($row[0] != '') {
          // $values = \Drupal::entityQuery('node')->condition('title', $row[0])->condition('field_customer', $form_state->getValue('term'))->condition('type', 'asset')->execute();
          $values = \Drupal::entityQuery('node')->condition('title', $row[0])->condition('type', 'asset')->execute();
          $node_not_exists = empty($values);
          if ($node_not_exists) {
            /*if node does not exist create new node*/
            $node = \Drupal::entityTypeManager()->getStorage('node')->create([
            // ===here asset is the content type mechine name.
              'type' => 'asset',
              'title' => $row[0],
              // 'field_company' => $row[1],
              // 'field_location' => $row[1],
              'field_employee_id' => $row[1],
              // 'field_status' => $row[3],
              'field_number_of_employee' => $row[2],
              'field_training_gap_count' => $row[3],
              'field_training_gap_score' => $row[4],
              'field_customer' => $form_state->getValue('term'),
            ]);
            if ($node->save()) {
              \Drupal::messenger()->addMessage($row[0] . ' imported successfully');
            }
          }
          else {
            /*if node exist update the node*/
            $nid = reset($values);
            $node = Node::load($nid);
            $node->setTitle($row[0]);
            // $node->set("field_company", $row[1]);
            // $node->set("field_location", $row[1]);
            $node->set("field_employee_id", $row[1]);
            // $node->set("field_status", $row[3]);
            $node->set("field_number_of_employee", $row[2]);
            $node->set("field_training_gap_count", $row[3]);
            $node->set("field_training_gap_score", $row[4]);
            // $node->set("field_customer", $form_state->getValue('term'));
            if ($node->save()) {
              \Drupal::messenger()->addMessage($row[0] . ' updated successfully');
            }
          }
        }
      }
    }
  }

}
