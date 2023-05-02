<?php

namespace Drupal\training_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class UploadCertificateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'upload_certificate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cid = \Drupal::request()->query->get('cid');
    $csid = \Drupal::request()->query->get('csid');
    $pid = \Drupal::request()->query->get('pid');
    $connection = \Drupal::database();
    $customerid = get_session_customerid();
    $query = $connection->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'title']);
    $query->condition('n.title', $csid);
    if (!empty($customerid)) {
      $query->join('node__field_customer', 'cus', 'n.nid = cus.entity_id');
      $query->condition('cus.field_customer_target_id', $customerid);
    }
    $result = $query->execute()->fetchAll();
    $options[$result[0]->nid] = $result[0]->title;
    $form['field_course_name'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => 'Course Name',
      '#default_value' => $result[0]->nid,
      '#required' => TRUE,
    ];
    $query = $connection->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'title']);
    $query->condition('n.title', $pid);
    if (!empty($customerid)) {
      $query->join('node__field_customer', 'cus', 'n.nid = cus.entity_id');
      $query->condition('cus.field_customer_target_id', $customerid);
    }
    $result = $query->execute()->fetchAll();
    $providers[$result[0]->nid] = $result[0]->title;
    $form['provider_fieldset_container_cer']['field_provider_name_cer'] = [
      '#type' => 'select',
      '#options' => $providers,
      '#title' => 'Training Provider',
      '#default_value' => $result[0]->nid,
      '#required' => TRUE,
    ];
    $query = $connection->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'title']);
    $query->condition('n.title', $cid);
    if (!empty($customerid)) {
      $query->join('node__field_customer', 'cus', 'n.nid = cus.entity_id');
      $query->condition('cus.field_customer_target_id', $customerid);
    }
    $result = $query->execute()->fetchAll();
    $options1[$result[0]->nid] = $result[0]->title;
    $form['field_certificate_type'] = [
      '#type' => 'select',
      '#options' => $options1,
      '#title' => 'Certificate Type',
      '#default_value' => $result[0]->nid,
      '#required' => TRUE,
    ];
    $form['field_certificates'] = [
      '#type' => 'managed_file',
      '#title' => 'Certificates',
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['txt pdf doc docx bmp jpg xlsx'],
      ],
    ];
    $form['field_start_date'] = [
      '#type' => 'date',
      '#title' => 'Start Date',
      '#required' => TRUE,
    ];
    $form['field_expiry_date'] = [
      '#type' => 'date',
      '#title' => 'End Date',
      '#required' => TRUE,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and Close Booking'),
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['mark_processor']],
      '#id' => 'mark_processor',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = \Drupal::request()->query->get('uid');
    $cid = \Drupal::request()->query->get('cid');
    $query = \Drupal::database()->select('user__field_certificates', 'c')
      ->fields('c', ['field_certificates_target_id'])
      ->condition('c.entity_id', $uid);
    $query->join('paragraph__field_enable', 'e', 'e.entity_id = c.field_certificates_target_id');
    $query->fields('e', [
      'entity_id',
      'field_enable_value',
    ]);
    $query->condition('e.field_enable_value', 1);
    $query->join('paragraph__field_certificate_type', 'd', 'd.entity_id = c.field_certificates_target_id');
    $query->fields('d', ['field_certificate_type_target_id']);
    $query->join('node_field_data', 'n', 'd.field_certificate_type_target_id = n.nid');
    $query->condition('n.title', $cid);
    $enable = $query->execute()->fetchAll();
    $user = User::load($uid);
    $fid = $form_state->getValue('field_certificates')[0];
    $paragraph = Paragraph::create([
      'type' => 'certificates',
      'field_course_name' => $form_state->getValue('field_course_name'),
      'field_certificate_type' => $form_state->getValue('field_certificate_type'),
      'field_certificates' => $fid,
      'field_start_date' => $form_state->getValue('field_start_date'),
      'field_expiry_date' => $form_state->getValue('field_expiry_date'),
      'field_provider_name' => $form_state->getValue('field_provider_name_cer'),
    ]);
    $paragraph->save();
    $user->field_certificates[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    if ($user->save()) {
      foreach ($enable as $value) {
        $para = Paragraph::load($value->entity_id);
        $para->set('field_enable', '0');
        $para->save();
      }
      $query2 = \Drupal::database()->select('user__field_course_details', 'n')
        ->fields('n', ['field_course_details_target_id'])
        ->condition('n.entity_id', $uid);
      $query2->join('paragraph__field_course_', 'd', 'd.entity_id = n.field_course_details_target_id');
      $query2->fields('d', ['field_course__target_id']);
      $query2->join('paragraph__field_certificate_type', 'ce', 'ce.entity_id = n.field_course_details_target_id');
      $query2->fields('ce', ['field_certificate_type_target_id']);
      $query2->join('node_field_data', 'c', 'ce.field_certificate_type_target_id = c.nid');
      $query2->fields('c', ['title']);
      $query2->condition('c.title', $cid);
      $query2->join('paragraph__field_status', 't', 'n.field_course_details_target_id = t.entity_id');
      $query2->fields('t', ['entity_id', 'field_status_value']);
      $query2->join('paragraph__field_enable', 'e', 'e.entity_id = n.field_course_details_target_id');
      $query2->condition('e.field_enable_value', 1);
      $status = $query2->execute()->fetchAll();

      $p = Paragraph::load($status[0]->entity_id);
      $p->set('field_status', 'completed');
      $p->set('field_close_booking', '1');
      $p->set('field_enable', '0');
      $p->save();
      \Drupal::messenger()->addMessage($this->t('Certificate uploaded successfully'), 'status', TRUE);
    }
    else {
      \Drupal::messenger()->addMessage($this->t('Certificate not uploaded, please try again'), 'error', TRUE);
    }
  }

  // public function trainingProviderDropdownCallback(array $form, FormStateInterface $form_state) {
  //   return $form['provider_fieldset_container_cer'];
  // }

}
