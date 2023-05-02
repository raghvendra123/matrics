<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Provides a Course Update Resource.
 *
 * @RestResource(
 *   id = "courseupdate",
 *   label = @Translation("Course Update"),
 *   uri_paths = {
 *     "canonical" = "/courseupdate/{pid}",
 *     "create" = "/courseupdate/{pid}",
 *   }
 * )
 */
class CourseUpdateResource extends ResourceBase {

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
  public function get($pid) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $p = Paragraph::load($pid);
    $response = [
      'status' => $p->get('field_status')->getValue()[0]['value'],
      'cancelled_by' => $p->get('field_cancelled_by')->getValue()[0]['value'],
      'pid' => $pid,
    ];
    $code = 200;

    return new ResourceResponse($response, $code);
  }

  /**
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $response = ['message' => $this->t('Please Post Pid, Status and Cancelled by key.')];
    $code = 400;
    if (!empty($data)) {
      $pid = $data['pid'];
      $p = Paragraph::load($pid);
      $p->set('field_status', $data['status']);
      $p->set('field_cancelled_by', $data['cancelled_by']);
      if ($p->save()) {
        $response = ['message' => 'Course Status Updated'];
        $code = 200;
      }
      else {
        $response = ['message' => 'Course Status not Updated'];
        $code = 400;
      }
    }
    return new ResourceResponse($response, $code);
  }

}
