<?php

namespace Drupal\edit_profiles_perms;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\user\RoleInterface;

/**
 * Provides the permissions for edit_profiles_perms module.
 */
class EditProfilesPermsPermissions {

  use StringTranslationTrait;

  /**
   * Get Edit Profiles Permissions.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    // Generate permissions for each user role except authenticated and
    // anonymous.
    $permissions = [];
    /* @var $roles \Drupal\user\RoleInterface[] */
    $roles = user_roles(TRUE);
    unset($roles[RoleInterface::AUTHENTICATED_ID]);
    if (count($roles) < 1) {
      return $permissions;
    }

    foreach ($roles as $role) {
      $role_name = $role->label();
      $role_id = $role->id();
      $permissions["edit $role_id users profiles"] = [
        'title' => $this->t("Edit %role_name users profiles", ['%role_name' => $role_name]),
      ];
    }

    return $permissions;
  }

}
