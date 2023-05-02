<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;

/**
 * Provides a Swap User Resource.
 *
 * @RestResource(
 *   id = "swap",
 *   label = @Translation("Swap User"),
 *   uri_paths = {
 *     "canonical" = "/matrics_api/swap/{uid}/user/{id}/{asset_id}"
 *   }
 * )
 */
class SwapUserResource extends ResourceBase {

  /**
   * A current user instance which is logged in the session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;

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
    AccountProxyInterface $current_user
  ) {
    parent::__construct(
      $config,
      $module_id,
      $module_definition,
      $serializer_formats,
      $logger
    );
    $this->loggedUser = $current_user;
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
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($uid = NULL, $id = NULL, $asset_id = NULL) {
    $response = ['message' => t('Please check url')];
    $code = 400;
    if ($uid != NULL && $id != NULL && $asset_id != NULL) {
      $user = User::load($uid);
      $user->set('field_employee_status', "1");
      $user->save();

      $user = User::load($id);
      $user->set('field_employee_status', "2");
      $user->save();
      $service = \Drupal::service('matrics_schedule.reload_asset_job');
      $service = $service->sayHello($asset_id);
      $response = ['message' => t('User swaped succuessfully')];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

}
