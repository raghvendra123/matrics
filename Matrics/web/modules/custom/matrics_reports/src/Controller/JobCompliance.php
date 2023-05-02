<?php

namespace Drupal\matrics_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * An job compliance controller.
 */
class JobCompliance extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function jobcompliance($job = NULL) {
    $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
      ->fields('jt', ['entity_id'])->condition('jt.field_job_title_target_id', $job);
    $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
    $query2->fields('u', ['name', 'uid']);
    $query2->join('user__field_training_gap_score', 't', 'u.uid = t.entity_id');
    $query2->fields('t', ['field_training_gap_score_value']);
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
                $color = 'green';
              }
              elseif ($months <= 6 && $months >= 3) {
                $color = 'orange';
              }
              elseif ($months <= 3 && $months >= 1) {
                $color = 'yellow';
              }
              else {
                $color = 'red';
              }
              $row[$row_key]['date'] = $user_certificate[$certi];
              $row[$row_key]['color'] = $color;
              $row[$row_key]['cname'] = $certi;
            }
            $row_key++;
          }
          else {
            $row[$row_key]['date'] = '0';
            $row[$row_key]['color'] = 'red';
            $row[$row_key]['cname'] = $certi;
            $row_key++;
          }
        }
      }
      $output[$job]['user']['user_data'][$user->uid] = $row;
      $output[$job]['user']['user_score'][$user->uid] = $user->field_training_gap_score_value;
      $output[$job]['user']['user_location'][$user->uid] = $location[0]->field_location_locality;
    }

    return [
      '#theme' => 'job_scheduling_matrix',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
    ];

  }

}
