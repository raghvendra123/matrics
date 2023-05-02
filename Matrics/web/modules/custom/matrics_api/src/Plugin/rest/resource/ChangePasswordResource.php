<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\user\Entity\User;

/**
 * Provides a Change Password Resource.
 *
 * @RestResource(
 *   id = "change_password",
 *   label = @Translation("Change Password"),
 *   uri_paths = {
 *     "create" = "/change_password"
 *   }
 * )
 */
class ChangePasswordResource extends ResourceBase {

  /**
   * A current user instance which is logged in the session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;

  /**
   * The Password Hasher.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordHasher;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $config
   *   A configuration array which contains the information about the plugin instance.
   * @param string $module_id
   *   The module_id for the plugin instance.
   * @param mixed $module_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A currently logged user instance.
   */
  public function __construct(
      array $config,
      $module_id,
      $module_definition,
      array $serializer_formats,
      LoggerInterface $logger,
      AccountProxyInterface $current_user,
      PasswordInterface $password_hasher
  ) {
    parent::__construct(
        $config,
        $module_id,
        $module_definition,
        $serializer_formats,
        $logger
    );
    $this->loggedUser = $current_user;
    $this->passwordHasher = $password_hasher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $config, $module_id, $module_definition) {
    return new static(
      $config,
      $module_id,
      $module_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('matrics_api'),
      $container->get('current_user'),
      $container->get('password')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $response = ['message' => $this->t('Please Post current password, password and confirm password key.')];
    $code = 400;
    if (!empty($data)) {
      $current_pass_input = trim($data['current_password']);
      if ($current_pass_input) {
        $user = User::load(\Drupal::currentUser()->id());
        if (!$this->passwordHasher->check($current_pass_input, $user->getPassword())) {
          $response = ['message' => $this->t('The current password you provided is incorrect.')];
          $code = 400;
        }
        else {
          if (isset($data['password']) && isset($data['password']) && $data['password'] == $data['confirm_password']) {
            $user = User::load(\Drupal::currentUser()->id());
            $user->setPassword($data['confirm_password']);
            $user->save();
            $user1 = \Drupal::currentUser();
            \Drupal::moduleHandler()->invokeAll('user_logout', [$user]);
            \Drupal::service('session_manager')->destroy();
            $user1->setAccount(new AnonymousUserSession());
            $response = ['message' => $this->t('Your password has been changed.')];
            $code = 200;
          }
          else {
            $response = ['message' => $this->t('The specified passwords do not match.')];
            $code = 400;
          }
        }
      }
    }
    return new ResourceResponse($response, $code);
  }

}
