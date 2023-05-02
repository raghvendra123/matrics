<?php

namespace Drupal\matrics_schedule\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class JobschedulingForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'job_matrics_reports';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $job = NULL, $user = NULL, $asset = NULL) {
    $connection = \Drupal::database();
    $query = \Drupal::database()->select('users_field_data', 'u');
    $query->fields('u', ['uid', 'name']);
    $query->condition('u.uid', '1', '!=');
    $query->condition('u.uid', $user, '!=');
    $query->join('user__field_job_title', 'jt', 'jt.entity_id = u.uid');
    $query->fields('jt', ['entity_id']);
    $query->condition('jt.field_job_title_target_id', $job);
    $query->join('user__field_employee_status', 'fa', 'fa.entity_id = u.uid');
    $query->condition('fa.field_employee_status_value', 2, '=');
    $query->fields('fa', ['entity_id']);
    $result = $query->execute()->fetchAll();
    foreach ($result as $value) {
      $option[$value->uid] = $value->name;
    }
    $service = \Drupal::service('matrics_schedule.default_job');
    $service = $service->sayHello($job, $user, $asset);
    if (!empty($option)) {
      $data = render($service);
    }
    else {
      $data = 'No record found!';
    }
    if (!empty($option)) {
      $form['user'] = [
        '#type' => 'select',
        '#options' => $option,
        '#title' => 'Name',
        '#required' => 'required',
        // 'callback' => '::list_employee',
        '#ajax' => [
          'callback' => '::list_employee',
          'wrapper' => 'job-box-container',
        ],
      ];
    }

    $form['job'] = [
      '#type' => 'hidden',
      '#title' => 'Name',
      '#required' => 'required',
      '#value' => $job,
    ];

    $form['swap_user'] = [
      '#type' => 'hidden',
      '#title' => 'Name',
      '#required' => 'required',
      '#value' => $user,
    ];
    $form['asset'] = [
      '#type' => 'hidden',
      '#title' => 'Name',
      '#required' => 'required',
      '#value' => $asset,
    ];

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'job-box-container'],
      '#weight' => '9',
    ];
    // The box contains some markup that we can change on a submit request.
    $form['container']['box'] = [
      '#type' => 'markup',
      '#markup' => $data,
    ];
    return $form;

  }

  /**
   * Ajax callback for the color dropdown.
   */
  public function list_employee(array $form, FormStateInterface $form_state) {
    $job = $form_state->getValue('job');
    $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
      ->fields('jt', ['entity_id'])
      ->condition('jt.field_job_title_target_id', $job);
    $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
    $query2->fields('u', ['name', 'uid']);
    $query2->join('user__field_training_gap_score', 't', 'u.uid = t.entity_id');
    $query2->fields('t', ['field_training_gap_score_value']);
    $query2->join('user__field_employee_status', 'fa', 'fa.entity_id = u.uid');
    $query2->condition('fa.field_employee_status_value', 2, '=');
    if ($selectedValue = $form_state->getValue('user')) {
      $query2->condition('u.uid', $selectedValue);
    }
    $job_user = $query2->execute()->fetchAll();
    $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
    $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
    $query3->condition('fc.entity_id', $job);
    $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
    $query3->fields('n', ['title']);
    $certi = $query3->execute()->fetchAll();
    $node = Node::load($job);
    $output[$job]['job_title'] = $node->getTitle();
    $certificate_name = [];
    $certificate_name['0'] = '';
    foreach ($certi as $cer) {
      $certificate_name[get_job_title_name($cer->field_certificate_target_id)] = get_job_title_name($cer->field_certificate_target_id);
    }
    foreach ($certi as $certificate) {
      $output[$job]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
    }
    foreach ($job_user as $user) {
      $location = \Drupal::database()->select('user__field_location', 'l')
        ->fields('l', ['entity_id', 'field_location_locality'])
        ->condition('l.entity_id', $user->uid);
      $location = $location->execute()->fetchAll();
      $row = [];
      $certificate_list = '';
      $certificate_list = list_certificate($user->uid);
      $user_certificate = certificate_expiration_date($user->uid);
      $row[0] = $user->name;
      $row_key = 1;
      foreach ($certificate_name as $certi) {
        if (!empty($certi)) {
          if (in_array($certi, $certificate_list)) {
            $color = '';
            $field_expiry_date = strtotime($user_certificate[$certi]);
            $current_date = new DrupalDateTime();
            $current_date = strtotime($current_date);
            if ($field_expiry_date > $current_date) {
              $diff = abs($field_expiry_date - $current_date);
              $years = floor($diff / (365 * 60 * 60 * 24));
              $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
              if ($months >= 6 || $years != 0) {
                $color = '#C6EFCE';
              }
              elseif ($months <= 6 && $months >= 3) {
                $color = '#FFCE33';
              }
              elseif ($months <= 3 && $months >= 1) {
                $color = '#FFEB9C';
              }
              else {
                $color = '#FFC7CE';
              }
              $row[$row_key]['date'] = $user_certificate[$certi];
              $row[$row_key]['color'] = $color;
              $row[$row_key]['cname'] = $certi;

            }
            $row_key++;
          }
          else {
            $row[$row_key]['date'] = '0';
            $row[$row_key]['color'] = '#FFC7CE';
            $row[$row_key]['cname'] = $certi;
            $row_key++;
          }
        }
      }
      $output[$job]['user']['user_data'][$user->uid] = $row;
      $output[$job]['user']['user_score'][$user->uid] = $user->field_training_gap_score_value;
      $output[$job]['user']['user_location'][$user->uid] = $location[0]->field_location_locality;
      $output[$job]['user']['swap_user'][$user->uid] = $form_state->getValue('swap_user');
      $output[$job]['user']['asset'][$user->uid] = $form_state->getValue('asset');
    }
    $element = $form['container'];
    $element['box'] = [
      '#theme' => 'schedule_job',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
