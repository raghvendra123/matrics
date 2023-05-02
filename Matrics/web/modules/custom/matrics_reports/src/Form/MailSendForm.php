<?php

namespace Drupal\matrics_reports\Form;

use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class MailSendForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'matrics_reports';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $customerid = get_session_customerid();
    $ids = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('roles', 'individual_employee');
    if ($customerid) {
      $ids->condition('field_customer', $customerid);
    }
    $ids = $ids->execute();
    $users = User::loadMultiple($ids);
    $userlist = [];
    if (!empty($users)) {
      foreach ($users as $user) {
        $username = $user->get('field_employee_name')->getString();
        $mail = $user->get('mail')->getString();
        $userlist[$mail] = $username;
      }
    }
    $form['user_list'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => t('Select Users'),
      '#required' => FALSE,
      '#options' => $userlist,
      '#size' => 5,
      '#weight' => 0,
      '#default_value' => '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send Mail'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = \Drupal::request()->query->get('id');
    $node = Node::load($id);
    $csv_file = $node->get('field_file')->getValue()[0]['target_id'];
    $file = File::load($csv_file);
    $file_url = file_create_url($file->getFileUri());
    $path = '<p>Hello,</p>
      <p>You can download report using below link</p>
      <a href="' . $file_url . '">' . $file_url . '</a>';

    $user_list = $form_state->getValue('user_list');
    $to = "kevinpatidar@agile451.com ,";
    foreach ($user_list as $value) {
      $to .= $value . " ,";
    }
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'matrics_reports';
    $key = 'report_email';
    $params['message'] = $path;
    $params['title'] = 'Report File';
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      \Drupal::messenger()->addError('There was a problem sending your message and it was not sent');
    }
    else {
      \Drupal::messenger()->addStatus('Your message has been sent.');
    }
  }

}
