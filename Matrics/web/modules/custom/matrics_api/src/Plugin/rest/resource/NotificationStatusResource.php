<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a Notification Status Resource.
 *
 * @RestResource(
 *   id = "notification_status",
 *   label = @Translation("Notification Status"),
 *   uri_paths = {
 *     "canonical" = "/notification_status"
 *   }
 * )
 */
class NotificationStatusResource extends ResourceBase {

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
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $response = ['message' => $this->t('Please Post status key.')];
    $code = 400;
    if (!empty($data)) {
      $uid = \Drupal::currentUser()->id();
      $query = \Drupal::database()->select('notification_status', 'n')
        ->fields('n', ['uid'])->condition('n.uid', $uid);
      $result = $query->countQuery()->execute()->fetchField();
      if ($result == 0) {
        $insert = \Drupal::database()->insert('notification_status')
          ->fields([
            'status' => $data['status'],
            'uid' => $uid,
            'created' => date('Y-m-d H:i:s'),
          ])
          ->execute();
        $response = ['message' => $this->t('Status Updated.')];
      }
      else {
        $update = \Drupal::database()->update('notification_status')
          ->fields([
            'status' => $data['status'],
            'created' => date('Y-m-d H:i:s'),
          ])
          ->condition('uid', $uid)
          ->execute();
        $response = ['message' => $this->t('Status Updated.')];
      }
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

}
