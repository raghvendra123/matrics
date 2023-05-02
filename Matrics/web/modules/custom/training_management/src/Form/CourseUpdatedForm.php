<?php

namespace Drupal\training_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class CourseUpdatedForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'course_updated_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pid = NULL, $uid = NULL, $cid = NULL, $status = NULL) {
    $p = Paragraph::load($pid);
    $status = $p->get('field_status')->getValue() ? $p->get('field_status')->getValue()[0]['value'] : '';
    $cancelled = $p->get('field_cancelled_by')->getValue() ? $p->get('field_cancelled_by')->getValue()[0]['value'] : '';
    $cancelled_fee = $p->get('field_cancelled_fee')->getValue() ? $p->get('field_cancelled_fee')->getValue()[0]['value'] : '';
    $form['status'] = [
      '#type' => 'select',
      '#title' => 'Status',
      '#options' => [
        'course booked' => 'Course booked',
        'completed' => 'Passed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'not required' => 'Not required',
      ],
      '#default_value' => $status,
    ];
    $form['cancelled_by'] = [
      '#type' => 'select',
      '#title' => 'Cancelled by',
      '#options' => [
        '' => 'None',
        'Cancelled by provider' => 'Cancelled by provider',
        'Cancelled by Customer' => 'Cancelled by Customer',
      ],
      '#default_value' => $cancelled,
      '#states' => [
        'visible' => [
          ':input[name="status"]' => ['value' => 'cancelled'],
        ],
      ],
    ];
    $form['cancelled_fee'] = [
      '#type' => 'textfield',
      '#title' => 'Cancelled Fee',
      '#default_value' => $cancelled_fee,
      '#states' => [
        'visible' => [
          ':input[name="status"]' => ['value' => 'cancelled'],
        ],
      ],
    ];
    $form['pid'] = [
      '#type' => 'hidden',
      '#default_value' => $pid,
    ];
    $form['uid'] = [
      '#type' => 'hidden',
      '#default_value' => $uid,
    ];
    $form['cid'] = [
      '#type' => 'hidden',
      '#default_value' => $cid,
    ];
    $form['status1'] = [
      '#type' => 'hidden',
      '#default_value' => $status,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save',
    ];

    if ($p->get('field_status')->getValue()[0]['value'] == 'not required') {
      $form['submit1'] = [
        '#type' => 'markup',
        '#markup' => '<a href="/close-booking-delete/' . $pid . '" class="button js-form-submit form-submit book-button">Remove Booking</a>',
        '#weight' => '3',
      ];
    }
    else {
      $form['submit1'] = [
        '#type' => 'markup',
        '#markup' => '<a href="/close-booking/' . $pid . '/' . $uid . '/' . $cid . '/' . $status . '" class="button js-form-submit form-submit book-button">Close Booking</a>',
        '#weight' => '3',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $pid = $form_state->getValue('pid');
    $p = Paragraph::load($pid);
    $p->set('field_course_result', $form_state->getValue('result'));
    $p->set('field_status', $form_state->getValue('status'));
    $p->set('field_cancelled_by', $form_state->getValue('cancelled_by'));
    $p->set('field_cancelled_fee', $form_state->getValue('cancelled_fee'));
    if ($form_state->getValue('status') == 'completed') {
      $p->set('field_close_booking', 0);  
    }
    if ($p->save()) {
      \Drupal::messenger()->addMessage($this->t('Updated course successfully'), 'status', TRUE);
    }
    else {
      \Drupal::messenger()->addMessage($this->t('Course not updated, please try again'), 'error', TRUE);
    }
    $response = new RedirectResponse('/training-management');
    $response->send();
  }

}
