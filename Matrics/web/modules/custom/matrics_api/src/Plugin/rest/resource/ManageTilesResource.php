<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a Manage Tiles Resource.
 *
 * @RestResource(
 *   id = "manage_tiles",
 *   label = @Translation("Manage Tiles"),
 *   uri_paths = {
 *     "canonical" = "/manage/tiles",
 *     "create" = "/manage/tiles",
 *   }
 * )
 */
class ManageTilesResource extends ResourceBase {

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
    $database = \Drupal::database();
    $query = $database->select('dashboard_manage', 'dm');
    $query->fields('dm');
    $result = $query->execute();
    $result = $result->fetchAll();

    $data = [];
    foreach ($result as $id => $row1) {
      $data[$row1->tile_order] = [
        'tid' => $row1->id,
        'uid' => $row1->uid,
        'tiles' => $row1->tiles,
        'settings' => $row1->settings,
        'tile_order' => $row1->tile_order,
        'status' => $row1->status,
        'chart_status' => $row1->chart_status,
      ];
    }
    $table = [];
    foreach ($data as $id => $row1) {
      if (!is_array($row1)) {
        $row1 = [];
      }
      $row1 += [
        'tid' => $row1['tid'],
        'parent' => '',
        'weight' => $row1['tile_order'],
        'depth' => 0,
        'status' => $row1['status'],
        'chart_status' => $row1['chart_status'],
        'classes' => [],
        'draggable' => TRUE,
      ];
      $tiles = $row1['tiles'];

      if (!empty($row1['draggable'])) {
        $row1['classes'][] = 'draggable';
      }
      $table['table'][$id] = [
        'id' => $id,
        'parent' => $row1['parent'],
        'depth' => $row1['depth'],
        'weight' => isset($row1['weight']) ? $row1['weight'] : $row1['tile_order'],
        'tiles' => $tiles,
        'status' => $row1['status'],
        'chart_status' => $row1['chart_status'],
        'tid' => $row1['tid'],
      ];
    }
    $table1 = $table;
    $code = 200;
    $response = new ResourceResponse($table1);
    $response->addCacheableDependency($table1);
    return $response;
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
    $test_table = [];
    foreach ($data['table'] as $row) {
      $test_table[$row['id']] = $row;
      $num_updated = \Drupal::database()->update('dashboard_manage')
        ->fields([
          'tile_order' => $row['weight'],
          'status' => $row['status'],
          'chart_status' => $row['chart_status'],
        ])
        ->condition('id', $row['tid'], '=')
        ->execute();
    }
    $response = ['message' => 'Updated records successfully'];
    $code = 200;
    return new ResourceResponse($response, $code);
  }

}
