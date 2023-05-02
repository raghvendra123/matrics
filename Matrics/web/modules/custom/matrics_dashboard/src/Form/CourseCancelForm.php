<?php

namespace Drupal\matrics_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class CourseCancelForm extends FormBase {

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
    $referer = \Drupal::request()->headers->get('referer');
    $form['status'] = [
      '#type' => 'hidden',
      '#default_value' => 'cancelled',
    ];
    $form['markup'] = [
      '#type' => 'markup',
      '#markup' => '<p>Are you sure you want to cancel this course?</p>',
    ];
    $form['pid'] = [
      '#type' => 'hidden',
      '#default_value' => $pid,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Yes',
    ];
    $form['no'] = [
      '#type' => 'markup',
      '#markup' => '<a href="'.$referer.'" class="button js-form-submit form-submit book-button">No</a>',
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
    $p->set('field_cancelled_by', 'Cancelled by Customer');
    $p->save();
    $uid = \Drupal::currentUser()->id();
    $response = new RedirectResponse('/user/' . $uid);
    $response->send();
  }

}
