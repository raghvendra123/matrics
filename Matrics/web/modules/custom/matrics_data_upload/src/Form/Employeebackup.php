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
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'title']);
    $query->condition('n.type', 'asset');
    $result = $query->execute()->fetchAll();
    foreach ($result as $value) {
      $option[$value->nid] = $value->title;
    }

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
    if (!empty($option)) {
      $form['asset'] = [
        '#type' => 'select',
        '#options' => $option,
        '#title' => 'Asset',
        '#required' => 'required',
      ];
    }
    $form['excel_file'] = [
      '#type' => 'managed_file',
      '#name' => 'excel_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t('Excel format only'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://',
    ];
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
    foreach ($rows as $row) {
      $asset = $form_state->getValue('asset');
      $query1 = \Drupal::database()->select('node_field_data', 'n');
      $query1->fields('n', ['nid', 'title']);
      $query1->join('node__field_asset', 'a', 'a.entity_id = n.nid');
      $query1->condition('n.type', 'job_title');
      $query1->condition('n.title', strtolower($row[3]));
      $query1->condition('a.field_asset_target_id', $asset);
      $job_title = $query1->execute()->fetchAll();
      $values = \Drupal::entityQuery('user')
        ->condition('name', $row[0])
        ->range(0, 1)
        ->execute();
      $user_not_exists = empty($values);
      if ($user_not_exists) {
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $user = User::create();
        $user->setPassword($row[0]);
        $user->enforceIsNew();
        $user->setEmail($row[2]);
        $user->setUsername($row[0]);
        // This username must be unique and accept only a-Z,0-9, - _ @.
        $user->set('field_employee_name', $row[0]);
        $user->set('field_employee_id', $row[1]);
        $user->set("init", 'mail');
        $user->set("langcode", $language);
        $user->set("preferred_langcode", $language);
        $user->set("preferred_admin_langcode", $language);
        $user->addRole('individual_employee');
        $user->set('field_asset', $form_state->getValue('asset'));
        $user->set('field_job_title', $job_title[0]->nid);
        $user->activate();
        $user->save();
        _user_mail_notify('register_no_approval_required', $user);
      }
    }
    \Drupal::messenger()->addMessage('imported successfully');
  }

}
