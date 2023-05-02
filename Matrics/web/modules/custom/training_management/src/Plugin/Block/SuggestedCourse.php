<?php

namespace Drupal\training_management\Plugin\Block;

use Drupal\Component\Serialization\Json;

/**
 * @file
 * Contains \Drupal\training_management\Plugin\Block\SuggestedCourse.
 */

use Drupal\Core\Block\BlockBase;

/**
 * Training Management Chart .
 *
 * @Block(
 *   id = "suggested_course",
 *   admin_label = @Translation("Suggested Coursed"),
 * )
 */
class SuggestedCourse extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_user = \Drupal::currentUser()->id();
    $uid = \Drupal::routeMatch()->getRawParameter('user');
    $query = \Drupal::database()->select('notify_course', 'c');
    $query->fields('c', ['course_id', 'user_id']);
    if ($current_user == 1) {
      $user = \Drupal::routeMatch()->getParameter('user');
      $uid = $user->id();
      $query->condition('c.user_id', $uid);
    }
    else {
      $query->condition('c.user_id', $current_user);
    }
    $result = $query->execute()->fetchAll();
    $index = 0;
    foreach ($result as $keys => $value) {
      $data = Json::decode($value->course_id);
      $rowkey = 0;
      foreach ($data as $key => $val) {
        if ($rowkey == 0) {
          $course_id = $val['course1'];
          $provider_id = $val['provider_name1'];
          $location = $val['location1'];
          $course_date = $val['course_date1'];
        }
        elseif ($rowkey == 1) {
          $course_id = $val['course2'];
          $provider_id = $val['provider_name2'];
          $location = $val['location2'];
          $course_date = $val['course_date2'];
        }
        elseif ($rowkey == 2) {
          $course_id = $val['course3'];
          $provider_id = $val['provider_name3'];
          $location = $val['location3'];
          $course_date = $val['course_date3'];
        }
        $course = \Drupal::database()->select('node_field_data', 'n')
          ->fields('n', ['title'])
          ->condition('n.nid', $course_id)->execute()->fetch();
        $provider_name = \Drupal::database()->select('node_field_data', 'n')
          ->fields('n', ['title'])
          ->condition('n.nid', $provider_id)->execute()->fetch();
        $output[$index][1] = $course->title;
        $output[$index][2] = $provider_name->title;
        $output[$index][3] = $location;
        $output[$index][4] = $course_date;
        $rowkey++;
        $index++;
      }
    }
    return [
      '#theme' => 'suggested_course',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
    ];
  }

}
