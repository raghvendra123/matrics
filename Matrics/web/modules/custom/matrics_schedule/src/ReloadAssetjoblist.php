<?php

namespace Drupal\matrics_schedule;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Job list.
 */
class ReloadAssetjoblist {

  /**
   * {@inheritdoc}
   */
  protected $saySomething;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->saySomething = 'Hello World!';
  }

  /**
   * {@inheritdoc}
   */
  public function sayHello($asset = '') {
    $query = \Drupal::database()->select('user__field_asset', 'n')
      ->fields('n', ['entity_id'])
      ->condition('n.field_asset_target_id', $asset);
    $query->join('users_field_data', 'u', 'u.uid = n.entity_id');
    $query->fields('u', ['name', 'uid']);
    $query->join('user__field_employee_status', 'us', 'us.entity_id = n.entity_id');
    $query->condition('us.field_employee_status_value', '1');
    $user = $query->execute()->fetchAll();

    foreach ($user as $uid) {
      $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
        ->fields('jt', ['field_job_title_target_id'])
        ->condition('jt.entity_id', $uid->entity_id);
      $job_title = $query2->execute()->fetchAll();

      $score = \Drupal::database()->select('user__field_training_gap_score', 'jt')
        ->fields('jt', ['field_training_gap_score_value'])
        ->condition('jt.entity_id', $uid->entity_id);
      $gap_score = $score->execute()->fetchAll();

      $job_title_name = get_job_title_name($job_title[0]->field_job_title_target_id);

      $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
      $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
      $query3->condition('fc.entity_id', $job_title[0]->field_job_title_target_id);
      $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
      $query3->fields('n', ['title']);
      $certi = $query3->execute()->fetchAll();

      $certificate_list = list_certificate($uid->entity_id);
      $user_certificate = certificate_expiration_date($uid->entity_id);

      $row[$uid->entity_id][0] = '<a href="/user/' . $uid->entity_id . '" target="_blank"> ' . $uid->name . '</a>';
      $row[$uid->entity_id][1] = $job_title_name;
      $row[$uid->entity_id][2] = "<a href='/job-scheduling/" . $job_title[0]->field_job_title_target_id . "/" . $uid->entity_id . "/" . $asset . "' class='use-ajax reserve'  data-dialog-options='{&quot;width&quot;:800}' data-dialog-type='modal' >Reserves</a>";
      $row_key = 3;
      $row_key2 = 0;
      $color = '';
      foreach ($certi as $certi_name) {
        if (!empty($certi_name->title)) {
          if (in_array($certi_name->title, $certificate_list)) {
            $color = '';
            $field_expiry_date = strtotime($user_certificate[$certi_name->title]);
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
              $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title . "(" . $user_certificate[$certi_name->title] . ")";
              $row[$uid->entity_id][$row_key][$row_key2]['color'] .= $color;
            }
            else {
              $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title . "(" . $user_certificate[$certi_name->title] . ")";
              $row[$uid->entity_id][$row_key][$row_key2]['color'] .= '#FFC7CE';
            }
            $row_key2++;
          }
          else {
            $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title;
            $row[$uid->entity_id][$row_key][$row_key2]['color'] .= '#FFC7CE';
            $row_key2++;
          }
        }
      }
      $row[$uid->entity_id]['score'] = $gap_score[0]->field_training_gap_score_value . "%";
      $output = $row;
    }

    // $element = $form['container'];
    // $element['date']['#prefix'] = '<div class="forcast_date">';
    $element['box']['table'] = [
      '#theme' => 'schedule_current',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
      '#weight' => '1',
    ];

    $element['box2']['table'] = [
      '#theme' => 'schedule_forcast',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
      '#weight' => '3',
    ];

    return $element;
  }

}
