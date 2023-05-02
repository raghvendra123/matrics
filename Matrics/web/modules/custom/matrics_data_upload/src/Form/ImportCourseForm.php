<?php

namespace Drupal\matrics_data_upload\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class ImportCourseForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'course_import';
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
    ];
    $form['excel_file'] = [
      '#type' => 'managed_file',
      '#name' => 'excel_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('Note: Excel format only. Download Sample File from <a href="../../../modules/custom/matrics_data_upload/sheet/course.xlsx" download>click here </a>'),
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
    unset($rows[0]);
    // Echo "<pre>";print_r($rows);exit;
    $msg = '';
    $msg1 = '';
    foreach ($rows as $row) {
      if ($row[0] != '') {
        $certificates_id = explode(',', $row[3]);

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
        $query1 = \Drupal::database()->select('node_field_data', 'n');
        $query1->fields('n', ['nid', 'title']);
        $query1->condition('n.type', 'vendor');
        $query1->condition('n.title', $row[6]);
        $query1->join('node__field_customer', 'c', 'n.nid = c.entity_id');
        $query1->condition('c.field_customer_target_id', $form_state->getValue('term'));
        $result1 = $query1->execute()->fetchAll();
        if (empty($result1)) {
          $msg1 = 'Provider is not available for this customer';
          \Drupal::messenger()->addError($row[0] . 'Provider is not available for this customer');
        }
      }
    }
    if ($msg == '' && $msg1 == '') {
      foreach ($rows as $row) {
        if ($row[0] != '') {
          $values = \Drupal::entityQuery('node')->condition('title', $row[0])->condition('type', 'courses')->execute();
          $node_not_exists = empty($values);
          $certificates_id = explode(',', $row[3]);
          $query1 = \Drupal::database()->select('node_field_data', 'n');
          $query1->fields('n', ['nid', 'title']);
          $query1->condition('n.type', 'vendor');
          $query1->condition('n.title', $row[6]);
          $query1->join('node__field_customer', 'c', 'n.nid = c.entity_id');
          $query1->condition('c.field_customer_target_id', $form_state->getValue('term'));
          $result1 = $query1->execute()->fetchAll();
          if ($node_not_exists) {
            $node = \Drupal::entityTypeManager()->getStorage('node')->create([
              // ===here asset is the content type mechine name.
              'type' => 'courses',
              'title' => $row[0],
              'field_course_id' => $row[1],
              'field_course_availability' => $row[2],
              // 'field_certificates' => $row[3],
              'field_delivery_method' => $row[4],
              'field_cm' => $row[5],
              'field_customer' => $form_state->getValue('term'),
            ]);
            $paragraph = Paragraph::create([
              'type' => 'provider_details',
              'field_provider_name' => $result1[0]->nid,
              'field_cost' => $row[7],
              'field_location' => [
                'country_code' => $row[8],
                'locality' => $row[9],
                'postal_code' => $row[10],
              ],
              'field_course_duration' => $row[11],
              'field_accreditation' => $row[12],
              'field_course_discounted' =>  $row[13],
              'field_num_bookings' =>  $row[14],
              'field_tm' =>  $row[15],
            ]);
            $paragraph->save();
            $node->field_provider_details[] = [
              'target_id' => $paragraph->id(),
              'target_revision_id' => $paragraph->getRevisionId(),
            ];
            $certificates_id = explode(',', $row[3]);
            foreach ($certificates_id as $certificate) {
              $node->field_certificates[] = $certificate;
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
            $node->set("field_course_id", $row[1]);
            $node->set("field_course_availability", $row[2]);
            // $node->set("field_customer", $form_state->getValue('term'));
            $certificates_id = explode(',', $row[3]);
            if (!empty($node->get('field_certificates')->getValue())) {
              foreach ($node->get('field_certificates')->getValue() as $certificate) {
                if (in_array($certificate['target_id'], $certificates_id)) {
                  $key = array_search($certificate['target_id'], $certificates_id);
                  unset($certificates_id[$key]);
                }
              }
            }
            if (!empty($node->get('field_provider_details')->getValue())) {
              foreach ($node->get('field_provider_details')->getValue() as $provider) {
                $prvdr = Paragraph::load($provider['target_id']);
                if (!empty($prvdr)) {
                  $p_name = $prvdr->get('field_provider_name')->getValue() ? $prvdr->get('field_provider_name')->getValue()[0]['target_id'] : '';
                  if ($p_name == $result1[0]->nid) {
                    $prvdr->set('field_provider_name', $result1[0]->nid);
                    $prvdr->set('field_cost', $row[7]);
                    $prvdr->set('field_location', [
                      'country_code' => $row[8],
                      'locality' => $row[9],
                      'postal_code' => $row[10],
                    ]);
                    $prvdr->set('field_course_duration', $row[11]);
                    $prvdr->set('field_accreditation', $row[12]);
                    $prvdr->set('field_course_discounted', $row[13]);
                    $prvdr->set('field_num_bookings', $row[14]);
                    $prvdr->set('field_tm', $row[15]);
                    $prvdr->save();
                  }
                }
              }
            }
            if (!empty($certificates_id)) {
              foreach ($certificates_id as $certificate) {
                $node->field_certificates[] = $certificate;
              }
            }
            // $node->set("field_certificates", $row[3]);
            $node->set("field_delivery_method", $row[4]);
            $node->set("field_cm", $row[5]);
            if ($node->save()) {
              \Drupal::messenger()->addMessage($row[0] . ' updated successfully');
            }
          }
        }
      }
    }
    else {
      \Drupal::messenger()->addError($msg);
    }
  }

}
