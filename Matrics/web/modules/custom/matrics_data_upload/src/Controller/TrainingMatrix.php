<?php

namespace Drupal\matrics_data_upload\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * {@inheritdoc}
 */
class TrainingMatrix extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function training() {
    $query = \Drupal::database()->select('node_field_data', 'n')
      ->fields('n', ['nid', 'title'])
      ->condition('n.status', 1)
      ->condition('n.type', 'asset');
    $asset = $query->execute()->fetchAll();
    foreach ($asset as $ass) {
      $query1 = \Drupal::database()->select('node__field_asset', 'fa')
        ->fields('fa', ['entity_id'])
        ->condition('fa.field_asset_target_id', $ass->nid)
        ->condition('fa.bundle', 'job_title');
      $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
      $query1->fields('n', ['title']);
      $job_title = $query1->execute()->fetchAll();
      foreach ($job_title as $job) {
        $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
          ->fields('jt', ['entity_id'])
          ->condition('jt.field_job_title_target_id', $job->entity_id);
        $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
        $query2->fields('u', ['name', 'uid']);
        $job_user = $query2->execute()->fetchAll();
        $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
        $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
        $query3->condition('fc.entity_id', $job->entity_id);
        $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
        $query3->fields('n', ['title']);
        $certi = $query3->execute()->fetchAll();
        $output[$ass->title][$job->entity_id]['job_title'] = $job->title;
        $certificate_name = [];
        $certificate_name['0'] = '';
        foreach ($certi as $cer) {
          $certificate_name[get_job_title_name($cer->field_certificate_target_id)] = get_job_title_name($cer->field_certificate_target_id);
        }
        foreach ($certi as $certificate) {
          $output[$ass->title][$job->entity_id]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
        }

        foreach ($job_user as $user) {
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
                }
                else {
                  $row[$row_key]['date'] = $user_certificate[$certi];
                  $row[$row_key]['color'] = 'red';
                }
                $row_key++;
              }
              else {
                $row[$row_key]['date'] = '0';
                $row[$row_key]['color'] = 'red';
                $row_key++;
              }
            }
          }
          $output[$ass->title][$job->entity_id]['user'][$user->uid] = $row;
        }
      }
    }
    return [
      '#theme' => 'training_matrix',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
    ];
  }

}
