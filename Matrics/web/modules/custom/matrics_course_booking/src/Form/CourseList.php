<?php

namespace Drupal\matrics_course_booking\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class CourseList extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'course_list_table';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $job = NULL, $user = NULL, $asset = NULL) {
    $uid = \Drupal::request()->query->get('uid');
    $certificate = \Drupal::request()->query->get('certificate');
    $query1 = \Drupal::database()->select('booked_course', 'n')
      ->fields('n')->condition('n.uid', $uid);
    $result = $query1->execute()->fetchAll();
    
    if (count($result) != 0) {
      $header = [
        'cname' => t('Course Name'),
        'pname' => t('Provider Name'),
        'location' => t('Location'),
        'expiry' => t('Expiry Date'),
      ];
      foreach ($result as $val) {
        $output[$val->uid] = [
          'cname' => $val->course_name,
          'pname' => $val->provider_name,
          'location' => $val->location,
          'expiry' => $val->expiry_date,
        ];
      }
      // print_r($output);die;
      $form['mymarkup'] = [
        '#type' => 'markup',
        '#markup' => '<h4>Employee has requested the below course:</h4>',
      ];
      $form['table'] = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $output,
        // '#empty' => t('No users found'),
      ];
    }
    else {
      $customerid = get_session_customerid();
      $courses = get_courses_by_certificate($certificate, NULL , $customerid);
      $form['course1'] = [
        '#type' => 'fieldset',
        '#open' => TRUE,
        '#attributes' => ['class' => ['row']],
      ];
      $form['course1']['course1'] = [
        '#type' => 'select',
        '#title' => 'Course',
        '#options' => $courses,
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::providerDropdownCallback1',
          'wrapper' => 'provider-fieldset1',
        ],
      ];

      $providers1 = get_providers_by_course($certificate, $form_state->getValue('course1'), NULL, $customerid);
      $form['course1']['provider_fieldset1'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'provider-fieldset1'],
      ];
      $form['course1']['provider_fieldset1']['provider_name1'] = [
        '#type' => 'select',
        '#title' => 'Provider Name',
        '#options' => $providers1,
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];
      $form['course1']['location1'] = [
        '#type' => 'textfield',
        '#title' => 'Location',
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];
      $form['course1']['course_date1'] = [
        '#type' => 'date',
        '#title' => 'Course Date',
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];

      $form['course2'] = [
        '#type' => 'fieldset',
        '#open' => TRUE,
        '#attributes' => ['class' => ['row']],
      ];
      $form['course2']['course2'] = [
        '#type' => 'select',
        '#title' => 'Course',
        '#options' => $courses,
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::providerDropdownCallback2',
          'wrapper' => 'provider-fieldset2',
        ],
      ];
      $providers2 = get_providers_by_course($certificate, $form_state->getValue('course2'), NULL, $customerid);

      $form['course2']['provider_fieldset2'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'provider-fieldset2'],
      ];
      $form['course2']['provider_fieldset2']['provider_name2'] = [
        '#type' => 'select',
        '#title' => 'Provider Name',
        '#options' => $providers2,
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];
      $form['course2']['location2'] = [
        '#type' => 'textfield',
        '#title' => 'Location',
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];
      $form['course2']['course_date2'] = [
        '#type' => 'date',
        '#title' => 'Course Date',
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];

      $form['course3'] = [
        '#type' => 'fieldset',
        '#open' => TRUE,
        '#attributes' => ['class' => ['row']],
      ];
      $form['course3']['course3'] = [
        '#type' => 'select',
        '#title' => 'Course',
        '#options' => $courses,
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
        '#ajax' => [
          'callback' => '::providerDropdownCallback3',
          'wrapper' => 'provider-fieldset3',
        ],
      ];

      $providers3 = get_providers_by_course($certificate, $form_state->getValue('course3'), NULL, $customerid);
      $form['course3']['provider_fieldset3'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'provider-fieldset3'],
      ];
      $form['course3']['provider_fieldset3']['provider_name3'] = [
        '#type' => 'select',
        '#title' => 'Provider Name',
        '#options' => $providers3,
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];
      $form['course3']['location3'] = [
        '#type' => 'textfield',
        '#title' => 'Location',
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];
      $form['course3']['course_date3'] = [
        '#type' => 'date',
        '#title' => 'Course Date',
        '#prefix' => "<div class='col-md-3'>",
        '#suffix' => "</div>",
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#name' => 'proceed',
        '#button_type' => 'primary',
        '#value' => $this->t('Proceed'),
        '#weight' => 10,
      ];
    }
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = \Drupal::request()->query->get('uid');
    $accr = \Drupal::request()->query->get('accr');
    $exp = \Drupal::request()->query->get('exp');
    $certificate = \Drupal::request()->query->get('certificate');
    $data[1]['course1'] = $form_state->getValues()['course1'];
    $data[1]['provider_name1'] = $form_state->getValues()['provider_name1'];
    $data[1]['location1'] = $form_state->getValues()['location1'];
    $data[1]['course_date1'] = $form_state->getValues()['course_date1'];
    $data[2]['course2'] = $form_state->getValues()['course2'];
    $data[2]['provider_name2'] = $form_state->getValues()['provider_name2'];
    $data[2]['location2'] = $form_state->getValues()['location2'];
    $data[2]['course_date2'] = $form_state->getValues()['course_date2'];
    $data[3]['course3'] = $form_state->getValues()['course3'];
    $data[3]['provider_name3'] = $form_state->getValues()['provider_name3'];
    $data[3]['location3'] = $form_state->getValues()['location3'];
    $data[3]['course_date3'] = $form_state->getValues()['course_date3'];
    $serializer = \Drupal::service('serializer');
    $data_ser = $serializer->serialize($data, 'json');

    $query = \Drupal::database()->select('notify_course', 'n')
      ->fields('n', ['user_id', 'accreditation', 'type'])->condition('n.user_id', $uid)->condition('n.type', 'suggested_course');
    $result = $query->countQuery()->execute()->fetchField();
    if ($result == 0) {
      $insert = \Drupal::database()->insert('notify_course')
        ->fields([
          'course_id' => $data_ser,
          'user_id' => $uid,
          'accreditation' => $accr,
          'expiry' => $exp,
          'certificate' => $certificate,
          'type' => 'suggested_course'
        ])
        ->execute();
      \Drupal::messenger()->addMessage(t('Suggestions sent.'), TRUE);
    }
    else {
      $update = \Drupal::database()->update('notify_course')
        ->fields([
          'course_id' => $data_ser,
        ])
        ->condition('user_id', $uid)
        ->condition('type', 'suggested_course')
        ->execute();
      \Drupal::messenger()->addMessage(t('Suggestions sent.'), TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function providerDropdownCallback1(array $form, FormStateInterface $form_state) {
    return $form['course1']['provider_fieldset1'];
  }

  /**
   * {@inheritdoc}
   */
  public function providerDropdownCallback2(array $form, FormStateInterface $form_state) {
    return $form['course2']['provider_fieldset2'];
  }

  /**
   * {@inheritdoc}
   */
  public function providerDropdownCallback3(array $form, FormStateInterface $form_state) {
    return $form['course3']['provider_fieldset3'];
  }

}
