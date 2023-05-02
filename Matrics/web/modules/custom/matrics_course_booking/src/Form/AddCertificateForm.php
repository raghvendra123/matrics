<?php

namespace Drupal\matrics_course_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class AddCertificateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_certificate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $uid = \Drupal::request()->query->get('uid');
    $user = User::load($uid);
    $asset = $user->get('field_asset')->getValue()[0]['target_id'];
    $job_title = $user->get('field_job_title')->getValue()[0]['target_id'];
    $customerid = $user->get('field_customer')->getValue() ? $user->get('field_customer')->getValue()[0]['target_id'] : '';
    $cids = [];
    if (!empty($user->get('field_certificates')->getValue())) {
      foreach ($user->get('field_certificates')->getValue() as $certificates) {
        $certificat = Paragraph::load($certificates['target_id']);
        if (!empty($certificat)) {
          $cids[] = $certificat->get('field_certificate_type')->getValue()[0]['target_id'];
        }
      }
    }
    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->fields('n');
    $query->condition('n.nid', $job_title);
    $query->join('node__field_asset', 'a', 'n.nid = a.entity_id');
    $query->fields('a', ['field_asset_target_id']);
    $query->condition('a.field_asset_target_id', $asset);
    $query->join('node__field_certificate', 'c', 'n.nid = c.entity_id');
    $query->fields('c', ['field_certificate_target_id']);
    $query->join('node_field_data', 'nd', 'nd.nid = c.field_certificate_target_id');
    $query->fields('nd', ['nid', 'title']);
    if (!empty($customerid)) {
      $query->join('node__field_customer', 'cus', 'n.nid = cus.entity_id');
      $query->condition('cus.field_customer_target_id', $customerid);
    }
    $certificates = $query->execute()->fetchAll();
    // dump($certificates);die;
    $options[''] = '- None -';
    foreach ($certificates as $certificate) {
      if (!in_array($certificate->nid, $cids)) {
        $options[$certificate->nid] = $certificate->title;
      }
    }
    $form['field_certificate_type'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => 'Certificate Type',
      '#ajax' => [
        'callback' => '::courseDropdownCallback',
        'wrapper' => 'course-fieldset-container',
      ],
      "#required" => TRUE,
    ];
    $courses = [];
    if (!empty($form_state->getValue('field_certificate_type'))) {
      $courses = get_courses_by_certificate('', $form_state->getValue('field_certificate_type'));
    }
    $form['course_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'course-fieldset-container'],
    ];
    $form['course_fieldset_container']['field_course_name'] = [
      '#type' => 'select',
      '#options' => $courses,
      '#title' => 'Course Name',
      "#required" => TRUE,
      '#ajax' => [
        'callback' => '::trainingProviderDropdownCallback',
        'wrapper' => 'provider-fieldset-container-certificate',
      ],
      '#validated' => TRUE,
    ];
    $providers = get_providers_by_course('', $form_state->getValue('field_course_name'), $form_state->getValue('field_certificate_type'));
    $form['provider_fieldset_container_certificate'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'provider-fieldset-container-certificate'],
    ];
    $form['provider_fieldset_container_certificate']['field_provider_name_cer'] = [
      '#type' => 'select',
      '#options' => $providers,
      '#title' => 'Training Provider',
      '#validated' => TRUE,
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
      '#value' => $this->t('Add Certificate'),
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['mark_processor']],
      '#id' => 'mark_processor',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array & $form, FormStateInterface $form_state) {
    $uid = \Drupal::request()->query->get('uid');
    $user = \Drupal\user\Entity\User::load($uid);
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
      drupal_flush_all_caches();
      \Drupal::messenger()->addMessage($this->t('Certificate added successfully'), 'status', TRUE);
    }
    else {
      \Drupal::messenger()->addMessage($this->t('Certificate not added, please try again'), 'error', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function courseDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['course_fieldset_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function trainingProviderDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['provider_fieldset_container_certificate'];
  }

}
