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
class BookCourseForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'book_course_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $uid = \Drupal::request()->query->get('uid');
    $certificate = \Drupal::request()->query->get('certificate');
    $c_type = \Drupal::request()->query->get('c_type');
    $customerid = get_session_customerid();
    $query = \Drupal::database()->select('user__field_course_details', 'u');
    $query->fields('u', ['field_course_details_target_id']);
    $query->condition('u.entity_id', $uid);
    $query->join('paragraph__field_certificate_type', 'd', 'd.entity_id = u.field_course_details_target_id');
    $query->fields('d', ['field_certificate_type_target_id']);
    $query->condition('d.field_certificate_type_target_id', $c_type);
    $query->join('paragraph__field_enable', 'e', 'e.entity_id = u.field_course_details_target_id');
    $query->condition('e.field_enable_value', 1);
    $cids = $query->execute()->fetchAll();
    $default = '';
    $courses = get_courses_by_certificate($certificate, NULL, $customerid);
    $start_date = '';
    $end_date = '';
    $course_price = 0;
    $tms_charge = '';
    $comments = '';
    if (!empty($cids)) {
      $p = Paragraph::load($cids[0]->field_course_details_target_id);
      $default = $p->get('field_course_')->getValue() ? $p->get('field_course_')->getValue()[0]['target_id'] : 0;
      $start_date = $p->get('field_start_date')->getValue() ? $p->get('field_start_date')->getValue()[0]['value'] : '';
      $end_date = $p->get('field_expiry_date')->getValue() ? $p->get('field_expiry_date')->getValue()[0]['value'] : '';
      $course_price = $p->get('field_list_price')->getValue() ? $p->get('field_list_price')->getValue()[0]['value'] : 0;
      $tms_charge = $p->get('field_discount_price')->getValue() ? $p->get('field_discount_price')->getValue()[0]['value'] : '';
      $comments = $p->get('field_comments')->getValue() ? $p->get('field_comments')->getValue()[0]['value'] : '';
    }
    else {
      $query1 = \Drupal::database()->select('booked_course', 'n')
        ->fields('n')->condition('n.uid', $uid);
      $result = $query1->execute()->fetchAll();
      if (count($result) != 0) {
        $default = $result[0]->cid;
        $query = \Drupal::database()->select('node_field_data', 'n')->fields('n',['nid'])->condition('n.title',$result[0]->provider_name);
        $defaultp_name = $query->execute()->fetchAll();
        $defaultprovider_name = $defaultp_name[0]->nid;
      }
    }
    $form['field_course_'] = [
      '#type' => 'select',
      '#options' => $courses,
      '#title' => 'Course Name',
      '#default_value' => $default,
      '#ajax' => [
        'callback' => '::providerDropdownCallback',
        'wrapper' => 'provider-fieldset-container',
      ],
      '#required' => TRUE,
    ];
    $provide_default = '';
    if ($form_state->getValue('field_course_')) {
      $providers = get_providers_by_course($certificate, $form_state->getValue('field_course_'), NULL, $customerid);
    }
    else {
      $providers = get_providers_by_course($certificate, $default, NULL, $customerid);
      foreach ($providers as $key => $value) {
        $provide_default = $key;
      }
    }

    $form['provider_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'provider-fieldset-container'],
    ];
    $form['provider_fieldset_container']['field_provider_name'] = [
      '#type' => 'select',
      '#options' => $providers,
      '#title' => 'Provider Name',
      '#default_value' => $provide_default ? $provide_default : '',
      '#ajax' => [
        'callback' => '::countryDropdownCallback',
        'wrapper' => 'country-fieldset-container',
      ],
      '#required' => TRUE,
      '#validated' => TRUE,
    ];
    // $form['field_certificate_type'] = array(
    //   '#type' => 'select',
    //   '#options' => $options1,
    //   '#title' => 'Certificate Type',
    // );
    $country_default = '';
    $city_default = '';
    $country[''] = '- None -';
    $city[''] = '- None -';
    $course_price1 = 0;
    if (!empty($form_state->getValue('field_provider_name'))) {
      $location = get_country_city($certificate);
      $country = $location['country'];
      $city = $location['city'];

      foreach ($country as $key => $value) {
        $country_default = $key;
      }
      foreach ($city as $key => $value) {
        $city_default = $key;
      }
      $course_price1 = get_course_price_by_provider($certificate, $form_state->getValue('field_course_'), $form_state->getValue('field_provider_name'));
    }
    elseif ($default != '') {
      $location = get_country_city($certificate);
      $country = $location['country'];
      $city = $location['city'];

      foreach ($country as $key => $value) {
        $country_default = $key;
      }
      foreach ($city as $key => $value) {
        $city_default = $key;
      }
      if ($defaultprovider_name) {
        $course_price1 = get_course_price_by_provider($certificate, $default, $defaultprovider_name);
      }
    }
    $form['country_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'country-fieldset-container'],
    ];
    $form['country_fieldset_container']['country'] = [
      '#type' => 'select',
      '#options' => $country,
      '#default_value' => $country_default,
      '#title' => 'Country',
      '#required' => TRUE,
      '#validated' => TRUE,
    ];

    $form['country_fieldset_container']['city'] = [
      '#type' => 'select',
      '#options' => $city,
      '#default_value' => $city_default,
      '#title' => 'City',
      '#required' => TRUE,
      '#validated' => TRUE,
      '#states' => [
        // Show this textfield if any radio except 'other' is selected.
        'visible' => [
          ':input[name="field_location"]' => ['!value' => ''],
        ],
      ],
    ];

    $form['field_start_date'] = [
      '#type' => 'date',
      '#title' => 'Start Date',
      '#default_value' => $start_date,
      '#required' => TRUE,
    ];

    $form['field_expiry_date'] = [
      '#type' => 'date',
      '#title' => 'End Date',
      '#default_value' => $end_date,
      '#required' => TRUE,
    ];

    $form['country_fieldset_container']['field_list_price'] = [
      '#type' => 'textfield',
      '#title' => 'Course price',
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];
    if ($course_price1!=0) {
      $form['country_fieldset_container']['field_list_price']['#value'] = $course_price1;
    } else {
      $form['country_fieldset_container']['field_list_price']['#default_value'] = $course_price;
    }
    $form['field_discount_price'] = [
      '#type' => 'textfield',
      '#title' => 'TMS charge',
      '#default_value' => $tms_charge,
      '#required' => TRUE,
    ];
    $form['field_comments'] = [
      '#type' => 'textarea',
      '#title' => 'Comments',
      '#default_value' => $comments,
    ];
    $form['c_type'] = [
      '#type' => 'hidden',
      '#value' => $c_type,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Book Course'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uid = \Drupal::request()->query->get('uid');
    $accr = \Drupal::request()->query->get('accr');
    $c_type = \Drupal::request()->query->get('c_type');
    $query = \Drupal::database()->select('user__field_course_details', 'u');
    $query->fields('u', ['field_course_details_target_id']);
    $query->condition('u.entity_id', $uid);
    $query->join('paragraph__field_certificate_type', 'd', 'd.entity_id = u.field_course_details_target_id');
    $query->fields('d', ['field_certificate_type_target_id']);
    $query->condition('d.field_certificate_type_target_id', $c_type);
    $query->join('paragraph__field_enable', 'e', 'e.entity_id = u.field_course_details_target_id');
    $query->condition('e.field_enable_value', 1);
    $cids = $query->execute()->fetchAll();
    if (empty($cids[0]->field_course_details_target_id)) {
      $user = User::load($uid);
      $address = [
        'country_code' => $form_state->getValue('country'),
        'locality' => $form_state->getValue('city'),
      ];
        $paragraph = Paragraph::create([
        'type' => 'course',
        'field_course_' => $form_state->getValue('field_course_'),
        'field_provider_name' => $form_state->getValue('field_provider_name'),
        'field_certificate_type' => $form_state->getValue('c_type'),
        'field_location' => $address,
        'field_city' => $form_state->getValue('city'),
        'field_start_date' => $form_state->getValue('field_start_date'),
        'field_expiry_date' => $form_state->getValue('field_expiry_date'),
        'field_list_price' => $form_state->getValue('field_list_price'),
        'field_discount_price' => $form_state->getValue('field_discount_price'),
        'field_comments' => $form_state->getValue('field_comments'),
        'field_status' => 'course booked',
        'field_enable' => 1,
      ]);
      $paragraph->save();
      $user->field_course_details[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
      if ($user->save()) {
        if (!empty($cids)) {
          $p = Paragraph::load($cids[0]->field_course_details_target_id);
          if (!empty($p)) {
            $p->set('field_enable', 0);          
            $p->save();
          }
        }
        $insert = \Drupal::database()->insert('notify_course')
          ->fields([
            'user_id' => $uid,
            'pid' => $paragraph->id(),
            'accreditation' => $accr,
            'type' => 'booked_course',
          ])
          ->execute();
        \Drupal::messenger()->addMessage($this->t('Booked course successfully'), 'status', TRUE);
      }
      else {
        \Drupal::messenger()->addMessage($this->t('Course not booked, please try again'), 'error', TRUE);
      }
    }
    else {
      \Drupal::messenger()->addMessage($this->t('Course already booked for this certificate'), 'error', TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function providerDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['provider_fieldset_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function countryDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['country_fieldset_container'];
  }

}
