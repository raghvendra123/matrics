<?php

namespace Drupal\matrics_data_upload\Plugin\Block;

/**
 * @file
 * Contains \Drupal\matrics_data_upload\Plugin\Block\CertificateCheck.
 */

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provide Committee I am Part of block.
 *
 * @Block(
 *   id = "certificate_check",
 *   admin_label = @Translation("Certificate Check"),
 * )
 */
class CertificateCheck extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::routeMatch()->getParameter('user');
    if (!empty($user)) {
      $uid = $user->id();
      $account = User::load($uid);

      $asset = $account->field_asset->getValue()[0]['target_id'];
      $job_title = $account->field_job_title->getValue()[0]['target_id'];
      $job_title_name = get_job_title_name($job_title);

      $query = \Drupal::database()->select('node__field_certificate', 'fc');
      $query->fields('fc', ['entity_id', 'field_certificate_target_id']);
      $query->condition('fc.entity_id', $job_title);
      $result = $query->execute()->fetchAll();

      $certificate_name['0'] = '';
      foreach ($result as $value) {
        $certificate_name[get_job_title_name($value->field_certificate_target_id)] = get_job_title_name($value->field_certificate_target_id);
      }
      $certificate = list_certificate($uid);

      $row = [];
      $certificate_list = '';

      $certificate_list = list_certificate($uid);
      $row[0] = $job_title_name;
      $user_certificate = certificate_expiration_date_by_uid($uid);

      $row_key = 1;
      foreach ($certificate_name as $certi) {
        if (!empty($certi)) {
          if (in_array($certi, $certificate_list)) {
            $color = '';
            $field_expiry_date = !empty($user_certificate[$certi]) ? strtotime($user_certificate[$certi]) : 0;
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
              $row[$row_key]['uid'] = $uid;
            }
            else {
              $today_date = new DrupalDateTime();
              $today_date = $today_date->format('Y-m-d');
              $row[$row_key]['date'] = !empty($user_certificate[$certi]) ? $user_certificate[$certi] : $today_date;
              $row[$row_key]['color'] = '#FFC7CE';
              $row[$row_key]['uid'] = $uid;
            }
            $row_key++;
          }
          else {
            $row[$row_key]['date'] = '0';
            $row[$row_key]['color'] = 'lightgray';
            $row[$row_key]['uid'] = $uid;
            $row_key++;
          }
        }
      }

      $rows[0] = $row;

      $output['certificate'] = $certificate_name;
      $output['row'] = $rows;
      return [
        '#theme' => 'employee_matrix',
        '#items' => $output,
        '#cache' => ['max-age' => 0],
      ];
    }
  }

}
