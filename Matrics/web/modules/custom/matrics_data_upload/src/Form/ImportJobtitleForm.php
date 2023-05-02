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
class ImportJobtitleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'jobtitle_import';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $connection = \Drupal::database();

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
      '#ajax' => [
        'callback' => '::assetDropdownCallback',
        'wrapper' => 'asset-container',
      ],
    ];

    $option = [];
    if (!empty($form_state->getValue('term'))) {
      $query = \Drupal::database()->select('node_field_data', 'n');
      $query->fields('n', ['nid', 'title']);
      $query->condition('n.type', 'asset');
      $query->join('node__field_customer', 'c', 'n.nid = c.entity_id');
      $query->condition('c.field_customer_target_id', $form_state->getValue('term'));
      $result = $query->execute()->fetchAll();

      foreach ($result as $value) {
        $option[$value->nid] = $value->title;
      }
    }

    $form['asset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asset-container'],
    ];

    $form['asset_container']['asset'] = [
      '#type' => 'select',
      '#options' => $option,
      '#title' => 'Asset',
      '#required' => 'required',
      '#validated' => TRUE,
    ];

    $form['excel_file'] = [
      '#type' => 'managed_file',
      '#name' => 'excel_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('Excel format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
      '#description' => t('Note: Excel format only. Download Sample File from <a href="../../../modules/custom/matrics_data_upload/sheet/Jobtitles.xlsx" download>click here </a>'),
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
        $cells[] = $cell->getValue();
      }
      $rows[] = $cells;
    }
    unset($rows[0]);
    
    $msg = '';
    foreach ($rows as $row) {
      if (!empty($row)) {
        if ($row[0] != '') {
          $certificates_id = explode(',', $row[1]);

          foreach ($certificates_id as $certificate) {
            $query = \Drupal::database()->select('node_field_data', 'n');
            $query->fields('n', ['nid', 'title']);
            $query->condition('n.type', 'certificate');
            $query->condition('n.nid', $certificate);
            $query->join('node__field_customer', 'c', 'n.nid = c.entity_id');
            $query->condition('c.field_customer_target_id', $form_state->getValue('term'));
            $result = $query->execute()->fetchAll();
            if (empty($result)) {
              $msg = 'Certificates is not available for this customer';
            }
          }
        }
      }
    }
    
    if ($msg == '') {
      foreach ($rows as $row) {
        if (!empty($row)) {
          if ($row[0] != '') {
            $values = \Drupal::entityQuery('node')->condition('title', $row[0])->condition('type', 'job_title')->condition('field_asset', $form_state->getValue('asset'))->execute();
            $node_not_exists = empty($values);

            if ($node_not_exists) {
              /*if node does not exist create new node*/
              $node = \Drupal::entityTypeManager()->getStorage('node')->create([
              // ===here asset is the content type mechine name
                'type' => 'job_title',
                'title' => $row[0],
                'field_asset' => $form_state->getValue('asset'),
                'field_customer' => $form_state->getValue('term'),
              ]);
              $certificates_id = explode(',', $row[1]);
              foreach ($certificates_id as $certificate) {
                $node->field_certificate[] = $certificate;
              }
              if ($node->save()) {
                \Drupal::messenger()->addMessage($row[0] . ' imported successfully');
              }
            }
            else {
              /*if node exist update the node*/
              $nid = reset($values);
              $node = Node::load($nid);
              $node->setTitle($row[0]);
              $node->set('field_asset', $form_state->getValue('asset'));
              // $node->set("field_customer", $form_state->getValue('term'));
              $certificates_id = explode(',', $row[1]);
              if (!empty($node->get('field_certificate')->getValue())) {
                foreach ($node->get('field_certificate')->getValue() as $certificate) {
                  if (in_array($certificate['target_id'], $certificates_id)) {
                    $key = array_search($certificate['target_id'], $certificates_id);
                    unset($certificates_id[$key]);
                  }
                }
              }
              if (!empty($certificates_id)) {
                foreach ($certificates_id as $certificate) {
                  $node->field_certificate[] = $certificate;
                }
              }
              if ($node->save()) {
                \Drupal::messenger()->addMessage($row[0] . ' updated successfully');
              }
            }
          }
        }
      }
    }
    else {
      \Drupal::messenger()->addError($msg);
    }
    
  }

  /**
   * {@inheritdoc}
   */
  public function assetDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['asset_container'];
  }

}
