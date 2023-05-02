<?php

namespace Drupal\matrics_course_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class BulkCertificateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulk_certificate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $customerid = get_session_customerid();
    $uid = \Drupal::request()->query->get('uid');
    $user = User::load($uid);
    $asset = $user->get('field_asset')->getValue()[0]['target_id'];
    $job_title = $user->get('field_job_title')->getValue()[0]['target_id'];
    $customerid = $user->get('field_customer')->getValue() ? $user->get('field_customer')->getValue()[0]['target_id'] : '';
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
    // $options[''] = '- None -';
    if (!empty($certificates)) {
      foreach ($certificates as $certificate) {
        $options[$certificate->nid] = $certificate->title;
      }
      $form['certificates'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Certificate type'),
          $this->t('Course Name'),
          $this->t('Training Provider'),
          $this->t('Start Date'),
          $this->t('End Date'),
          $this->t('Certificates')
        ]
      ];
      $form['#prefix'] = '<div id="modal-form">';
      $form['#suffix'] = '</div>';
      $form['messages'] = [
          '#weight' => -9999,
          '#type' => 'status_messages',
      ];
      foreach ($certificates as $key => $certificate) {
        $options= [];
        $options[$certificate->nid] = $certificate->title;
        $courses = get_courses_by_certificate('', $certificate->nid);
        $providers = [];
        $providers[''] = '- None -';
        $type = '';
        $course = '';
        $provider = '';
        $start = '';
        $end = '';
        $file_id = '';
        if (!empty($form_state->getValue('certificates')[$key]['courses'])) {
          $providers = get_providers_by_course('', $form_state->getValue('certificates')[$key]['courses'], $certificate->nid);
        }
        // \Drupal::database()->select('user')
        if (!empty($user->get('field_certificates')->getValue())) {
          foreach ($user->get('field_certificates')->getValue() as $cert) {
            $paragraph = Paragraph::load($cert['target_id']);
            $enable = $paragraph->get('field_enable')->getValue() ? $paragraph->get('field_enable')->getValue()[0]['value'] : null;
            $certi = $paragraph->get('field_certificate_type')->getValue() ? $paragraph->get('field_certificate_type')->getValue()[0]['target_id'] : null;
            if ($enable == 1 && $certi==$certificate->nid) {
              $type = $certificate->nid;
              $course = $paragraph->get('field_course_name')->getValue() ? $paragraph->get('field_course_name')->getValue()[0]['target_id'] : '';
              $provider = $paragraph->get('field_provider_name')->getValue() ? $paragraph->get('field_provider_name')->getValue()[0]['target_id'] : '';
              $start = $paragraph->get('field_start_date')->getValue() ? $paragraph->get('field_start_date')->getValue()[0]['value'] : '';
              $end = $paragraph->get('field_expiry_date')->getValue() ? $paragraph->get('field_expiry_date')->getValue()[0]['value'] : '';
              $file_id = $paragraph->get('field_certificates')->getValue() ? $paragraph->get('field_certificates')->getValue()[0]['target_id'] : '';
              $url = '';
              if ($file_id) {
                $file = File::load($file_id);
                $url = $file->createFileUrl();
              }
            }
          }
        }
        if (!empty($course)) {
          $providers = get_providers_by_course('', $course, $certificate->nid);
        }
        $form['certificates'][$key] = [
          'certificate' => [
            '#type' => 'select',
            '#options' => $options,
            '#title' => 'Certificate Type',
            '#default_value' => $type,
          ],
          'courses' => [
            '#type' => 'select',
            '#options' => $courses,
            '#title' => 'Course Name',
            '#ajax' => [
              'callback' => '::trainingProviderDropdownCallback',
              'wrapper' => 'provider-fieldset-container-certificate' . $key,
            ],
            '#default_value' => $course,
          ],
          'container' => [
            '#type' => 'container',
            '#attributes' => ['id' => 'provider-fieldset-container-certificate' . $key],   
            'provider' => [
              '#type' => 'select',
              '#options' => $providers,
              '#title' => 'Training Provider',
              '#validated' => TRUE,
              '#default_value' => $provider,
            ],
          ],
          'start_date' => [
            '#type' => 'date',
            '#title' => 'Start Date',
            '#default_value' => $start,
          ],  
          'end_date' => [
            '#type' => 'date',
            '#title' => 'End Date',
            '#default_value' => $end,
          ],
          // 'file' => [
          //   '#type' => 'managed_file',
          //   '#title' => 'Certificates',
          //   '#default_value' => [$file_id],
          // ],
        ];
        if (!empty($file_id)) {
          $form['certificates'][$key]['markup'] = [
            '#type' => 'markup',
            '#markup' => '<a href="'.$url.'" class="upload-file-icon">Certificate</a>',
          ];
          $form['certificates'][$key]['file'] = [
            '#type' => 'hidden',
            '#title' => 'Certificates',
            '#value' => $file_id,
          ];
        }
        else {
          $form['certificates'][$key]['file'] = [
            '#type' => 'managed_file',
            '#title' => 'Certificates',
            '#default_value' => [$file_id],
            '#upload_validators' => [
              'file_validate_extensions' => ['txt pdf doc docx bmp jpg xlsx'],
            ],
          ];
        }
      }
      $form['uid'] = [
        '#type' => 'hidden',
        '#value' => $uid,
      ];
      $form['actions']['#type'] = 'actions';
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add Certificate'),
        '#button_type' => 'primary',
        '#attributes' => ['class' => ['mark_processor']],
        '#id' => 'mark_processor',
        '#ajax'  => [
          'callback' => [$this, 'mymodule_form_ajax_validate'],
          'wrapper' => 'modal-form',
          'progress' => [
            'type' => 'throbber',
            'message' => t('Saving...'),
          ],
        ],
      ];
      
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    }
    else {
      $form['blank'] = [
        '#type' => 'markup',
        '#markup' => 'No certificates found for this user',
      ];
    }
    return $form;
  }
  
  function mymodule_form_ajax_validate(&$form, $form_state) {
  $response = new AjaxResponse();

  if (!empty($form_state->getErrors())) {

    $response->addCommand(new ReplaceCommand('#modal-form', $form));
    return $response;
  }

  $uid = $form_state->getValue('uid');
  $response->addCommand(new RedirectCommand('/user/'. $uid));

  return $response;
}
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $course_msg = '';
    $provider_msg= '';
    $start_msg = '';
    $date_msg = '';
    $file_msg = '';
    $error_course = false;
    $error_provider = false;
    $error_start = false;
    $error_date = false;
    $error_file = false;
    foreach ($form_state->getValue('certificates') as $key => $row) {
        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->fields('n', ['title']);
        $query->condition('n.nid', $row['certificate']);
        $result = $query->execute()->fetchObject();
        
        if (!empty($row['courses']) || !empty($row['container']['provider']) || !empty($row['start_date']) || !empty($row['end_date']) || !empty($row['file'])) {
            if (empty($row['courses'])) {
                $form['certificates'][$key]['courses']['#attributes']['class'][] = 'error';
                $course_msg = $this->t('The course is not selected. Please select a course for certificates');
                $error_course = true;
            }
            if (empty($row['container']['provider'])) {
                $form['certificates'][$key]['container']['provider']['#attributes']['class'][] = 'error';
                $provider_msg = $this->t('The provider is not selected. Please select a provider for certificates');
                $error_provider = true;
            }
            if (empty($row['start_date'])) {
                $form['certificates'][$key]['start_date']['#attributes']['class'][] = 'error';
                $start_msg = $this->t('The start date is not filled. Please fill the start date for certificates');
                $error_start = true;
            }
            if (empty($row['end_date'])) {
                $form['certificates'][$key]['end_date']['#attributes']['class'][] = 'error';
                $date_msg = $this->t('The end date is not filled. Please fill the end date for certificates');
                $error_date = true;
            }
            if (empty($row['file'])) {
                $form['certificates'][$key]['file']['#attributes']['class'][] = 'error';
                $file_msg = $this->t('The file is not uploaded. Please upload a file for certificates');
                $error_file = true;
            }
        }
        // if (!empty($row['end_date']) && empty($row['file'])) {
        //     $form['certificates'][$key]['file']['#attributes']['class'][] = 'error';
        //     $file_msg = $this->t('The file is not uploaded. Please upload a file for certificates');
        //     $error_file = true;
        // }
        // if ($row['end_date'] == '' && !empty($row['file'])) {
        //     $form['certificates'][$key]['end_date']['#attributes']['class'][] = 'error';
        //     $date_msg = $this->t('The end date is not filled. Please fill the end date for certificates');
        //     $error_date = true;
        // }
    }
    if ($error_course==true) {
     $form_state->setErrorByName('courses', $course_msg);
    }
    if ($error_provider==true) {
     $form_state->setErrorByName('provider', $provider_msg);
    }
    if ($error_start==true) {
     $form_state->setErrorByName('start_date', $start_msg);
    }
    if ($error_date==true) {
     $form_state->setErrorByName('end_date', $date_msg);
    }
    if ($error_file==true) {
     $form_state->setErrorByName('file', $file_msg);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array & $form, FormStateInterface $form_state) {
    $uid = \Drupal::request()->query->get('uid');
    $user = \Drupal\user\Entity\User::load($uid);
    $target_id = [];
    if (!empty($user->get('field_certificates')->getValue())) {
        foreach ($user->get('field_certificates')->getValue() as $certificate) {
            $paragraph = Paragraph::load($certificate['target_id']);
            $enable = $paragraph->get('field_enable')->getValue() ? $paragraph->get('field_enable')->getValue()[0]['value'] : null;
            if ($enable==1) {
                $target_id[] = $paragraph->get('field_certificate_type')->getValue()[0]['target_id'];
            }
        }
    }
    foreach ($form_state->getValue('certificates') as $row) {
        if (!in_array($row['certificate'], $target_id)) {
            if(!empty($row['courses']) && !empty($row['container']['provider']) && !empty($row['start_date']) && !empty($row['end_date']) && !empty($row['file'])) {
                $fid = null;
                if (!empty($row['file'])) {
                    $fid = $row['file'][0];
                }
                $paragraph = Paragraph::create([
                  'type' => 'certificates',
                  'field_course_name' => $row['courses'],
                  'field_certificate_type' => $row['certificate'],
                  'field_certificates' => $fid,
                  'field_start_date' => $row['start_date'],
                  'field_expiry_date' => $row['end_date'],
                  'field_provider_name' => $row['container']['provider'],
                ]);
                $paragraph->save();
                $user->field_certificates[] = [
                  'target_id' => $paragraph->id(),
                  'target_revision_id' => $paragraph->getRevisionId(),
                ];
                $user->save();
            }
        }
    }
    \Drupal::messenger()->addMessage($this->t('Certificates added successfully'), 'status', TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function trainingProviderDropdownCallback(array $form, FormStateInterface $form_state) {
      
      $element = $form_state->getTriggeringElement();
      if (!empty($element)) {
          $name = $element['#name'];
          $key = (int)filter_var($name, FILTER_SANITIZE_NUMBER_INT);
          if ($name =='certificates['. $key .'][courses]') {
              $form_state->setRebuild(TRUE);
              return $form['certificates'][$key]['container']['provider'];
          }
      }
  }

}
