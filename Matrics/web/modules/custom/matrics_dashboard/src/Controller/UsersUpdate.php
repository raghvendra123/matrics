<?php

namespace Drupal\matrics_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides user update.
 */
class UsersUpdate extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function usersupdate() {
    $ids = \Drupal::entityQuery('user')
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->execute();
    $users = User::loadMultiple($ids);
    foreach ($users as $user) {
      $field_certificates = $user->field_certificates->getValue();
      $mail = $user->get('mail')->getString();
      $uid = $user->get('uid')->value;

      if ($user->field_certificates && $user->field_certificates->getValue() && $user->field_certificates->getValue()[0]) {
        $paragraph = $user->field_certificates->getValue()[0];
        $paragraph_entity = Paragraph::load($paragraph['target_id']);

        $field_expiry_date = strtotime($paragraph_entity->field_expiry_date->getValue()[0]['value']);
        $current_date = new DrupalDateTime();
        $current_date = strtotime($current_date);
        if ($field_expiry_date > $current_date) {
          $user = User::load($uid);
          $user->set('field_training_gap_score', "100");
          $user->save();
        }
        else {
          $user = User::load($uid);
          $user->set('field_training_gap_score', "0");
          $user->save();
        }
      }
    }
  }

}
