<?php

namespace Drupal\matrics_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class CourseUpdateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'job_matrics_reports';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pid = NULL) {
    $p = Paragraph::load($pid);
    $form['status'] = [
      '#type' => 'select',
      '#title' => 'Status',
      '#options' => ['completed' => 'completed', 'cancelled' => 'cancelled'],
      '#default_value' => $p->get('field_status')->getValue()[0]['value'],
    ];
    $form['cancelled_by'] = [
      '#type' => 'select',
      '#title' => 'Cancelled by',
      '#options' => [
        'Cancelled by provider' => 'Cancelled by provider',
        'Cancelled by Customer' => 'Cancelled by Customer',
      ],
      '#default_value' => $p->get('field_cancelled_by')->getValue()[0]['value'],
      '#states' => [
        'visible' => [
          ':input[name="status"]' => ['value' => 'cancelled'],
        ],
      ],
    ];
    $form['cancelled_fee'] = [
      '#type' => 'textfield',
      '#title' => 'Cancelled Fee',
      '#default_value' => $p->get('field_cancelled_fee')->getValue()[0]['value'],
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

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $pid = $form_state->getValue('pid');
    $p = Paragraph::load($pid);
    $p->set('field_status', $form_state->getValue('status'));
    $p->set('field_cancelled_by', $form_state->getValue('cancelled_by'));
    $p->set('field_cancelled_fee', $form_state->getValue('cancelled_fee'));
    $p->save();
    $response = new RedirectResponse('/booked-courses');
    $response->send();
  }

}
