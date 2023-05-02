<?php

namespace Drupal\Tests\edit_profiles_perms\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests the permissions provided by edit_profiles_perms module.
 *
 * @package Drupal\Tests\edit_profiles_perms\Functional
 *
 * @group edit_profiles_perms
 */
class EditProfilesPermsTest extends BrowserTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'edit_profiles_perms_test',
  ];

  /**
   * A user with the developer role.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $developer;

  /**
   * A user with the manager role.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $manager;

  /**
   * A user with the administrator role.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $admin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $developer = $this->drupalCreateUser();
    $developer->addRole('developer');
    $developer->save();
    $this->developer = $developer;

    $manager = $this->drupalCreateUser();
    $manager->addRole('manager');
    $manager->save();
    $this->manager = $manager;

    $this->admin = $this->drupalCreateUser([], NULL, TRUE);
  }

  /**
   * Tests Edit Profiles Permissions.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testEditProfilePerms() {
    // Assert that the roles defined by edit_profiles_perms_test module get
    // their permissions generated and appear correctly in the UI.
    $assert = $this->assertSession();
    $this->drupalLogin($this->admin);
    $this->drupalGet('admin/people/permissions');
    $assert->pageTextContains('Edit Profiles Permissions');
    $assert->pageTextContains('Edit Manager users profiles');
    $assert->pageTextContains('Edit Developer users profiles');
    $assert->checkboxChecked('developer[edit manager users profiles]');
    $assert->checkboxNotChecked('anonymous[administer users]');
    $assert->checkboxNotChecked('authenticated[administer users]');
    // Assert we are not generating permissions for authenticated nor anonymous
    // roles.
    $assert->pageTextNotContains('Edit Authenticated users profiles');
    $assert->pageTextNotContains('Edit Anonymous users profiles');

    // Tests for asserting edit to profiles based on our permissions.
    // - Developer and Manager roles have 'access user profiles' permission.
    // - The Developer role has 'edit manager users profiles' permission.
    // - The Manager role does not have permission to edit users profiles.
    //
    // By default Drupal requires 'administer users' permission in order to edit
    // any user profile.
    // Assert Developers can edit Managers profiles.
    $this->drupalLogin($this->developer);
    $this->drupalGet('user/' . $this->manager->id() . '/edit');
    $assert->statusCodeEquals(200);
    $this->submitForm(['mail' => 'test@example.com'], 'Save');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('The changes have been saved.');

    // Assert Managers can't edit developers profiles.
    $this->drupalLogin($this->manager);
    $this->drupalGet('user/' . $this->developer->id() . '/edit');
    $assert->statusCodeEquals(403);

    // Assert users with more than one role, and only one with access.
    $user = $this->drupalCreateUser();
    $user->addRole('developer');
    $user->addRole('manager');
    $user->save();
    $this->drupalLogin($this->developer);
    $this->drupalGet('user/' . $user->id() . '/edit');
    $assert->statusCodeEquals(200);

    // Assert that the 'administer users' permission overrides ours.
    $this->drupalLogin($this->admin);
    $this->drupalPostForm('admin/people/permissions', ['authenticated[administer users]' => TRUE], 'Save permissions');
    $assert->checkboxChecked('authenticated[administer users]');
    // Managers should now be able to edit Developers profiles.
    $this->drupalLogin($this->manager);
    $this->drupalGet('user/' . $this->developer->id() . '/edit');
    $assert->statusCodeEquals(200);

    // Assert any user can edit its own profile page.
    $this->drupalGet('user/' . $this->manager->id() . '/edit');
    $assert->statusCodeEquals(200);

    // An inactive/blocked user's profile should not affect by our permissions.
    $this->drupalLogout();
    $this->manager->block();
    $this->manager->save();
    $this->drupalLogin($this->developer);
    $this->drupalGet('user/' . $this->manager->id() . '/edit');
    $assert->statusCodeEquals(200);
  }

}
