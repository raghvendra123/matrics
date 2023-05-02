<?php

namespace Drupal\matrics_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\Core\Render\Markup;

/**
 * Asset complience.
 */
class AssetCompliance extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function assetompliance($asset = NULL) {
    $asset_data = [
      'asset' => $asset,
      'expiry_date' => date('Y-m-d'),
    ];

    $form['form'] = $this->formBuilder()->getForm('Drupal\matrics_reports\Form\ComplianceFilterForm', $asset_data);
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function getComplianceFilterResult($asset_data = []) {
    $asset = $asset_data['asset'];
    $expiry_date = $asset_data['expiry_date'];
    $customerid = get_session_customerid();
    $connection = \Drupal::database();
    $query = \Drupal::database()->select('users_field_data', 'u');
    $query->fields('u', ['uid', 'name']);
    $query->condition('u.uid', '1', '!=');
    $query->join('user__roles', 'r', 'r.entity_id = u.uid');
    $query->condition('r.roles_target_id', 'individual_employee', "=");
    if (!empty($customerid)) {
      $query->join('user__field_customer', 'cu', 'cu.entity_id = u.uid');
      $query->condition('cu.field_customer_target_id', $customerid);
    }
    $query->join('user__field_asset', 'fa', 'fa.entity_id = u.uid');
    $query->condition('fa.field_asset_target_id', $asset, '=');
    $query->fields('fa', ['entity_id']);
    $query->join('user__field_training_gap_score', 'ts', 'ts.entity_id = u.uid');
    $query->fields('ts', ['field_training_gap_score_value']);
    $query->join('user__field_employee_status', 'st', 'st.entity_id = u.uid');
    $query->condition('st.field_employee_status_value', 1, '=');
    $query->fields('st', ['field_employee_status_value']);
    $result = $query->execute()->fetchAll();
    $total_compliance = 0;
    $rows = [];
    foreach ($result as $key => $value) {
      $status = 0;
      $name = $value->name;
      $uid = $value->uid;
      $certi_results = $this->complianceScoreCalculation($uid, $expiry_date);
      $field_training_gap_score_value = $certi_results['training_gap_score'];

      $total_emp = $key + 1;
      $total_compliance = $total_compliance + $field_training_gap_score_value;
      $asset_compliance = number_format(($total_compliance / ($total_emp * 100)) * 100);
      if (!empty($value->field_employee_status_value)) {
        $status = "<span class='emp_status'>" . $value->field_employee_status_value . "</span>";
      }
      else {
        $status = "<span class='emp_status'>" . $value->field_employee_status_value . "</span>";
      }
      $entity_type_id = 'user';
      $field_name = 'field_employee_status';
      $entity_fields_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type_id);
      $field_definition = $entity_fields_definitions[$field_name];
      $field_allowed_options = options_allowed_values($field_definition);
      $status = $field_allowed_options[$value->field_employee_status_value];
      $rows[] = [Markup::create('<a target="_blank" href=/user/' . $uid . '>' . $name . '</a>'),
        $field_training_gap_score_value . "%",
        Markup::create("<span class='emp_status'>" . $value->field_employee_status_value . "</span>"),
      ];
    }
    $output = [
      'rows' => $rows,
      'total_compliance' => $total_compliance,
      'asset_compliance' => $asset_compliance,
      'total_emp' => $total_emp,
    ];
    return $output;
  }

  /**
   * Certificate expiry check.
   */
  public function complianceScoreCalculation($uid = NULL, $filter_date = '') {
    $output = [];
    if ($uid) {
      $user = User::load($uid);
      $training_gap_score = 0;
      $training_gap_count = 0;
      if ($user && $user->hasField('field_certificates')) {
        $certificate = list_certificate($uid);
        $exp_date = certificate_expiration_date($uid);
        $row = 0;
        $job_title = !empty($user->field_job_title->getValue()) ? $user->field_job_title->getValue()[0]['target_id'] : '';
        if ($job_title) {
          $query = \Drupal::database()->select('node__field_certificate', 'fc');
          $query->fields('fc', ['entity_id', 'field_certificate_target_id']);
          $query->condition('fc.entity_id', $job_title);
          $result = $query->execute()->fetchAll();
          $total_certificate = 0;
          $certificate_name = [];
          foreach ($result as $value) {
            $certificate_name[get_job_title_name($value->field_certificate_target_id)] = get_job_title_name($value->field_certificate_target_id);
            $total_certificate += 1;
          }
          if ($certificate_name) {
            foreach ($certificate_name as $certi) {
              if (!empty($certi)) {
                if (in_array($certi, $certificate)) {
                  $field_expiry_date = isset($exp_date[$certi]) ? strtotime($exp_date[$certi]) : 0;
                  $current_date = strtotime($filter_date);
                  if ($field_expiry_date > $current_date) {
                    $row += 1;
                  }
                }
              }
            }
          }
          if (!empty($row)) {
            $training_gap_score = number_format(($row / ($total_certificate)) * 100);
            $training_gap_count = number_format($total_certificate - $row);
          }
        }
      }
      $output = [
        'training_gap_score' => $training_gap_score,
        'training_gap_count' => $training_gap_count,
      ];
    }
    return $output;
  }

}
