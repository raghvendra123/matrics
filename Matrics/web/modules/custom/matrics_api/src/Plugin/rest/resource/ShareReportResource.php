<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a Share Report Resource.
 *
 * @RestResource(
 *   id = "share",
 *   label = @Translation("Share Report"),
 *   uri_paths = {
 *     "canonical" = "/share/{node_id}/report",
 *     "create" = "/share/{node_id}/report"
 *   }
 * )
 */
class ShareReportResource extends ResourceBase {

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
  public function get($node_id = NULL) {
    $response = ['message' => t('Please check url')];
    $code = 400;
    if ($node_id != NULL) {
      $db = \Drupal::database();
      $settings = \Drupal::configFactory()->get('nodeaccess.settings');
      $nid = $node_id;
      $role_alias = $settings->get('role_alias');
      $role_map = $settings->get('role_map');
      $allowed_roles = [];
      $user = \Drupal::currentUser();
      $allowed_grants = $settings->get('grants');
      unset($role_alias['anonymous']);
      $role_alias['authenticated']['name'] = 'To share for every one';
      $role_alias['authenticated']['alias'] = 'To share for every one';

      foreach ($role_alias as $id => $role) {
        if ($role['allow']) {
          $allowed_roles[] = $id;
        }
      }
      $form_values = [];
      // Load all roles.
      foreach ($role_alias as $id => $role) {
        $rid = $role_map[$id];
        $query = $db->select('node_access', 'n')
          ->fields('n', ['grant_view', 'grant_update', 'grant_delete'])
          ->condition('n.gid', $rid, '=')
          ->condition('n.realm', 'nodeaccess_rid', '=')
          ->condition('n.nid', $nid)
          ->execute();
        $result = $query->fetchAssoc();
        if (!empty($result)) {
          $form_values['rid'][$rid] = [
            'name' => $role['alias'],
            'grant_view' => (boolean) $result['grant_view'],
            'grant_update' => (boolean) $result['grant_update'],
            'grant_delete' => (boolean) $result['grant_delete'],
          ];
        }
        else {
          $form_values['rid'][$rid] = [
            'name' => $role['alias'],
            'grant_view' => FALSE,
            'grant_update' => FALSE,
            'grant_delete' => FALSE,
          ];
        }
      }

      // Load users from node_access.
      $query = $db->select('node_access', 'n');
      $query->join('users_field_data', 'ufd', 'ufd.uid = n.gid');
      $query->fields('n', ['grant_view', 'grant_update', 'grant_delete', 'nid']);
      $query->fields('ufd', ['name', 'uid']);
      $query->condition('n.nid', $nid, '=');
      $query->condition('n.realm', 'nodeaccess_uid', '=');
      $query->orderBy('ufd.name', 'ASC');
      $results = $query->execute();
      while ($account = $results->fetchObject()) {
        $form_values['uid'][$account->uid] = [
          'name' => $account->name,
          'keep' => 1,
          'grant_view' => $account->grant_view,
          'grant_update' => $account->grant_update,
          'grant_delete' => $account->grant_delete,
        ];
      }
      $response = [$form_values];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

  /**
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $response = ['message' => t('Please post nid key')];
    $code = 400;
    if (!empty($data)) {
      $db = \Drupal::database();
      // Update configuration.
      $nid = explode("/", $_SERVER['REQUEST_URI'])[2];
      $data['nid'] = $nid;
      $grants = [];
      $node = Node::load($nid);
      foreach (['uid', 'rid'] as $type) {
        $realm = 'nodeaccess_' . $type;
        if (isset($data[$type]) && is_array($data[$type])) {
          foreach ($data[$type] as $gid => $line) {
            $grant = [
              'gid' => $gid,
              'realm' => $realm,
              'grant_view' => empty($line['grant_view']) ? 0 : $line['grant_view'],
              'grant_update' => empty($line['grant_update']) ? 0 : $line['grant_update'],
              'grant_delete' => empty($line['grant_delete']) ? 0 : $line['grant_delete'],
            ];
            if ($grant['grant_view'] || $grant['grant_update'] || $grant['grant_delete']) {
              $grants[] = $grant;
            }
          }
        }
      }
      // Save role and user grants to our own table.
      $db->delete('nodeaccess')
        ->condition('nid', $nid)
        ->execute();
      foreach ($grants as $grant) {
        $id = $db->insert('nodeaccess')
          ->fields([
            'nid' => $nid,
            'gid' => $grant['gid'],
            'realm' => $grant['realm'],
            'grant_view' => $grant['grant_view'],
            'grant_update' => $grant['grant_update'],
            'grant_delete' => $grant['grant_delete'],
          ])
          ->execute();
      }
      \Drupal::entityTypeManager()->getAccessControlHandler('node')->acquireGrants($node);
      \Drupal::service('node.grant_storage')->write($node, $grants);
      $response = ['message' => t('Report has been shared succesfully.')];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

}
