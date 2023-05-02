<?php

namespace Drupal\matrics_data_upload\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Drupal\user\Entity\User;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class ImportEmployeeForm extends FormBase {

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
    $query1 = \Drupal::database()->select('node_field_data', 'n');
    $query1->fields('n', ['nid', 'title']);
    $query1->condition('n.type', 'job_title');
    $job_title = $query1->execute()->fetchAll();

    foreach ($job_title as $value1) {
      $job_title_option[$value1->nid] = $value1->title;
    }

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
      '#description' => t('Note: Excel format only. Download Sample File from <a href="../../../modules/custom/matrics_data_upload/sheet/employee.xlsx" download>click here </a>'),
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
    $msg1 = '';
    foreach ($rows as $row) {
      if (!empty($row)) {
        if ($row[0] != '') {
          $asset = $form_state->getValue('asset');
          $query1 = \Drupal::database()->select('node_field_data', 'n');
          $query1->fields('n', ['nid', 'title']);
          $query1->join('node__field_asset', 'a', 'a.entity_id = n.nid');
          $query1->condition('n.type', 'job_title');
          $query1->condition('n.title', strtolower($row[3]));
          $query1->condition('a.field_asset_target_id', $asset);
          $job_title = $query1->execute()->fetchAll();
          if (empty($job_title)) {
            $msg = 'Please add correct jobtitle for the assets';
            \Drupal::messenger()->addError('Please add correct jobtitle for the assets in user' . $row[0]);
          }
          $user = \Drupal::entityQuery('user')
            ->condition('name', $row[0])
            ->range(0, 1)
            ->execute();
            
          if (!empty($user)) {
            $msg1 = 'Username is already exist';
            \Drupal::messenger()->addError($row[0] . ' username is already exist');
          }
        }
      }
    }
    if (empty($msg) && empty($msg1)) {
      foreach ($rows as $row) {
        if (!empty($row)) {
          if ($row[0] != '') {
            $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
            $user = User::create();
            $user->setPassword($row[0]);
            $user->enforceIsNew();
            $user->setEmail($row[2]);
            // This username must be unique and accept only a-Z,0-9, - _ @.
            $user->setUsername($row[0]);
            $user->set('field_employee_name', $row[0]);
            $user->set('field_employee_id', $row[1]);
            $user->set("init", 'mail');
            $user->set("langcode", $language);
            $user->set("preferred_langcode", $language);
            $user->set("preferred_admin_langcode", $language);
            $user->addRole('individual_employee');
            $user->set('field_asset', $form_state->getValue('asset'));
            $user->set('field_job_title', $job_title[0]->nid);
            $user->set("field_customer", $form_state->getValue('term'));
            $user->activate();
            $user->save();
            _user_mail_notify('register_no_approval_required', $user);
            \Drupal::messenger()->addMessage($row[0] . ' imported successfully and use employee name as username and password');
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
