<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a Jobtitle Resource.
 *
 * @RestResource(
 *   id = "jobtitles",
 *   label = @Translation("Jobtitle List"),
 *   uri_paths = {
 *     "canonical" = "/jobtitles",
 *     "create" = "/jobtitles"
 *   }
 * )
 */
class JobtitleResource extends ResourceBase {

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
  public function get() {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $query1 = \Drupal::database()->select('node__field_asset', 'fa')
      ->fields('fa', ['entity_id', 'field_asset_target_id'])
      ->condition('fa.bundle', 'job_title');
    $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
    $query1->join('node_field_data', 'a', 'a.nid = fa.field_asset_target_id');
    $query1->fields('n', ['title']);
    $query1->fields('a', ['title']);
    $job_title = $query1->execute()->fetchAll();
    $result1 = [];
    $count = 0;
    foreach ($job_title as $element) {
      $result1[$element->a_title][] = $element;
    }

    foreach ($result1 as $key1 => $value1) {
      $option1[$count][$key1] = $key1;
      $count++;
      foreach ($value1 as $val) {
        $option1[$count]['id'] = $val->entity_id;
        $option1[$count]['value'] = ' - ' . $val->title;
        $count++;
      }
    }
    $response = ['jobtitles' => $option1];
    $code = 200;
    return new ResourceResponse($response, $code);
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
    $response = ['message' => $this->t('Please Post asset key.')];
    $code = 400;
    if (!empty($data)) {
      $options = [];
      // $options[0]['select'] = '- select -';
      if (!empty($data['asset_id'])) {
        $options[0]['id'] = 'all' . $data['asset_id'];
        $options[0]['value'] = 'Select all';
        $query1 = \Drupal::database()->select('node__field_asset', 'fa')
          ->fields('fa', ['entity_id'])
          ->condition('fa.field_asset_target_id', $data['asset_id'])
          ->condition('fa.bundle', 'job_title');
        $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
        $query1->fields('n', ['title']);
        $job_title = $query1->execute()->fetchAll();
        $count = 1;
        foreach ($job_title as $job) {
          $options[$count]['id'] = $job->entity_id;
          $options[$count]['value'] = $job->title;
          $count++;
        }
      }
      $response = ['jobtitles' => $options];
      $code = 200;
    }

    return new ResourceResponse($response, $code);
  }

}
