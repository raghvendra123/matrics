<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a Image Resource.
 *
 * @RestResource(
 *   id = "image_upload",
 *   label = @Translation("Profile Image"),
 *   serialization_class = "Drupal\file\Entity\File",
 *   uri_paths = {
 *     "conical" = "/image_upload",
 *     "create" = "/image_upload"
 *   }
 * )
 */
class ImageUpload extends ResourceBase {

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
  public function post(Request $request) {
    print_r($request);die;
    // if (!$this->loggedUser->hasPermission('access content')) {
    //   throw new AccessDeniedHttpException();
    // }
    // $node = Node::load($nid);
    // $node->set("field_task_status", 'completed');
    // $node->save();
    // $response = ['message' => $node->getTitle() . ' is mark as completed.'];
    $code = 200;
    return new ResourceResponse($data, $code);
  }

  /**
   * Responds to PATCH requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function patch(array $data) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    // $node = Node::load($nid);
    // $node->set("field_task_status", 'completed');
    // $node->save();
    // $response = ['message' => $node->getTitle() . ' is mark as completed.'];
    $code = 200;
    return new ResourceResponse($data, $code);
  }

}
