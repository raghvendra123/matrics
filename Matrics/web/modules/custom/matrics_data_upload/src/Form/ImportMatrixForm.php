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
class ImportMatrixForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'matrix_import';
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
      '#description' => t('Note: Excel format only. Download Sample File from <a href="../../../modules/custom/matrics_data_upload/sheet/Matrix.xlsx" download>click here </a>'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
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
    $certificate = $rows[0];
    unset($certificate[0]);
    $asset = $form_state->getValue('asset');

    $i = 1;
    $msg = FALSE;
    $msg1 = FALSE;
    foreach ($certificate as $key => $cert) {
      $query1 = \Drupal::database()->select('node_field_data', 'n');
      $query1->fields('n', ['nid', 'title']);
      $query1->condition('n.title', strtolower($cert));
      if (!empty($form_state->getValue('term'))) {
        $query1->join('node__field_customer', 'c', 'n.nid = c.entity_id');
        $query1->condition('c.field_customer_target_id', $form_state->getValue('term'));
      }
      $cert_id = $query1->execute()->fetchAll();
      if (!empty($cert_id)) {
        $certificate_id[$i] = $cert_id[0]->nid;
        $i++;
      }
      else {
        $msg = TRUE;
        \Drupal::messenger()->addError($cert . ' certificate is not avaialable for the customer');
      }
    }

    unset($rows[0]);
    foreach ($rows as $row) {
      $job_title = $row[0];
      //unset($row[0]);
      $query1 = \Drupal::database()->select('node_field_data', 'n');
      $query1->fields('n', ['nid', 'title']);
      $query1->condition('n.title', $job_title);
      if (!empty($form_state->getValue('term'))) {
        $query1->join('node__field_customer', 'c', 'n.nid = c.entity_id');
        $query1->condition('c.field_customer_target_id', $form_state->getValue('term'));
      }
      $jobtitle = $query1->execute()->fetchAll();
      if (empty($jobtitle)) {
        $msg1 = TRUE;
        \Drupal::messenger()->addError($job_title . ' jobtitle is not avaialable for the customer');
      }
    }
    if ($msg==FALSE && $msg1==FALSE) {
      foreach ($rows as $row) {
        $job_title = $row[0];
        unset($row[0]);
        $certificate_select = [];
        foreach ($row as $k => $cert_status) {
          $query1 = \Drupal::database()->select('node_field_data', 'n');
          $query1->fields('n', ['nid', 'title']);
          $cer_id = isset($certificate_id[$k]) ? $certificate_id[$k] : '';
          $query1->condition('n.nid', $cer_id);
          if (!empty($form_state->getValue('term'))) {
            $query1->join('node__field_customer', 'c', 'n.nid = c.entity_id');
            $query1->condition('c.field_customer_target_id', $form_state->getValue('term'));
          }
          $result = $query1->execute()->fetchAll();
          if ($cert_status == 1 && !empty($result)) {
            $certificate_select[] = $certificate_id[$k];
          }
        }

        $values = \Drupal::entityQuery('node')->condition('title', $job_title)->condition('type', 'job_title')->condition('field_asset', $form_state->getValue('asset'))->execute();
        $node_not_exists = empty($values);

        if (!empty($job_title)) {
          if ($node_not_exists) {
            /*if node does not exist create new node*/
            $node = \Drupal::entityTypeManager()->getStorage('node')->create([
            // ===here asset is the content type mechine name.
              'type' => 'job_title',
              'title' => $job_title,
              'field_asset' => $form_state->getValue('asset'),
              'field_certificate' => $certificate_select,
              'field_customer' => $form_state->getValue('term'),
            ]);
            $node->save();
            \Drupal::messenger()->addMessage('Imported successfully');
          }
          else {
            /*if node exist update the node*/
            $nid = reset($values);

            $node = Node::load($nid);
            $node->setTitle($job_title);
            $node->set('field_asset', $form_state->getValue('asset'));
            $node->set('field_certificate', $certificate_select);
            $node->set("field_customer", $form_state->getValue('term'));
            $node->save();
            \Drupal::messenger()->addMessage('Updated successfully');
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function assetDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['asset_container'];
  }

}
