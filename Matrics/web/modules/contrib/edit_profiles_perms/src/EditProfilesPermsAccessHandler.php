<?php

namespace Drupal\edit_profiles_perms;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserAccessControlHandler;

/**
 * Defines an access control handler for the user entity type.
 */
class EditProfilesPermsAccessHandler extends UserAccessControlHandler {

  /**s
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // Parent class allowing access takes precedence.
    /* @var $entity \Drupal\user\UserInterface */
    $access = parent::checkAccess($entity, $operation, $account);
    if ($access->isAllowed()) {
      return $access;
    }
    // Respect the conditions on update of the parent class.
    if ($operation == 'update' && !$entity->isAnonymous()) {
      foreach ($entity->getRoles(TRUE) as $role) {
        $permission_edit = AccessResult::allowedIfHasPermission($account, "edit $role users profiles");
        if ($permission_edit->isAllowed()) {
          return $permission_edit;
        }
      }
    }
    return $access;
  }

}
