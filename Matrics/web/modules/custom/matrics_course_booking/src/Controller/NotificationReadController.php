<?php

namespace Drupal\matrics_course_booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

/**
 * {@inheritdoc}
 */
class NotificationReadController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function read() {
    $uid = \Drupal::currentUser()->id();
    $update = \Drupal::database()->update('notify_course')
      ->fields([
        'status' => 1,
      ])
      ->condition('user_id', $uid)
      ->execute();
    $account = User::load($uid);
    $roles = $account->getRoles();
    if (in_array("mnager", $roles)) {
      $update = \Drupal::database()->update('notification')
        ->fields([
          'status' => 1,
        ])
        ->execute();
    }
    echo "test"; die;
  }

}
