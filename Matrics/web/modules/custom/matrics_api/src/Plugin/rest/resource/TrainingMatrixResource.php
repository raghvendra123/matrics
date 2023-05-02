<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a Training Matrix Resource.
 *
 * @RestResource(
 *   id = "training_matrix",
 *   label = @Translation("Training Matrix"),
 *   uri_paths = {
 *     "create" = "/training_matrix"
 *   }
 * )
 */
class TrainingMatrixResource extends ResourceBase {

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
    $response = ['message' => $this->t('Please Post asset key.')];
    $code = 400;
    if (!empty($data)) {
      if ($data['job_title_id'] == 'all') {
        $query1 = \Drupal::database()->select('node__field_asset', 'fa')
          ->fields('fa', ['entity_id'])
          ->condition('fa.field_asset_target_id', $data['asset_id'])
          ->condition('fa.bundle', 'job_title');
        $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
        $query1->fields('n', ['title']);
        $job_title = $query1->execute()->fetchAll();
        foreach ($job_title as $job) {
          $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
            ->fields('jt', ['entity_id'])
            ->condition('jt.field_job_title_target_id', $job->entity_id);
          $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
          $query2->fields('u', ['name', 'uid']);
          $job_user = $query2->execute()->fetchAll();

          $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
          $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
          $query3->condition('fc.entity_id', $job->entity_id);
          $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
          $query3->fields('n', ['title']);
          $certi = $query3->execute()->fetchAll();

          $output[$data['asset_id']][$job->entity_id]['job_title'] = $job->title;
          $certificate_name = [];
          $certificate_name['0'] = '';
          foreach ($certi as $cer) {
            $certificate_name[get_job_title_name($cer->field_certificate_target_id)] .= get_job_title_name($cer->field_certificate_target_id);
          }
          foreach ($certi as $certificate) {
            $output[$data['asset_id']][$job->entity_id]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
          }

          foreach ($job_user as $user) {
            $row = [];
            $certificate_list = '';
            $certificate_list = list_certificate($user->uid);
            $user_certificate = certificate_expiration_date($user->uid, 'all');
            $row[0] = $user->name;
            $row_key = 1;
            foreach ($certificate_name as $certi) {
              if (!empty($certi)) {
                if (in_array($certi, $certificate_list)) {
                  $color = '';
                  $field_expiry_date = strtotime($user_certificate[$certi]);
                  $current_date = new DrupalDateTime();
                  $current_date = strtotime($current_date);

                  if ($field_expiry_date > $current_date) {
                    $diff = abs($field_expiry_date - $current_date);
                    $years = floor($diff / (365 * 60 * 60 * 24));
                    $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                    $row[$row_key]['date'] .= date('d-m-Y', strtotime($user_certificate[$certi]));
                  }
                  else {
                    $row[$row_key]['date'] .= date('d-m-Y', strtotime($user_certificate[$certi]));
                  }
                  $row_key++;
                }
                else {
                  $row_key++;
                }
              }
            }
            $output[$data['asset_id']][$job->entity_id]['user'][$user->uid] = $row;
          }
        }
        if (empty($row)) {
          $output['data'] = 'There is no Employee related to selected Asset and Job Title. Please try with other Job Title or Asset.';
        }
      }
      else {
        $query1 = \Drupal::database()->select('node__field_asset', 'fa')
          ->fields('fa', ['entity_id'])
          ->condition('fa.field_asset_target_id', $data['asset_id'])
          ->condition('fa.bundle', 'job_title');
        $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
        $query1->fields('n', ['title']);
        $job_title = $query1->execute()->fetchAll();
        foreach ($job_title as $job) {
          if ($data["job_title_id"] == $job->entity_id) {
            $title_name = $job->title;
          }
        }

        $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
          ->fields('jt', ['entity_id'])
          ->condition('jt.field_job_title_target_id', $data['job_title_id']);
        $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
        $query2->fields('u', ['name', 'uid']);
        $job_user = $query2->execute()->fetchAll();

        $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
        $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
        $query3->condition('fc.entity_id', $data['job_title_id']);
        $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
        $query3->fields('n', ['title']);
        $certi = $query3->execute()->fetchAll();
        $output[$data['asset_id']][$data['job_title_id']]['job_title'] = $title_name;
        $certificate_name = [];
        $certificate_name['0'] = '';

        foreach ($certi as $cer) {
          $certificate_name[get_job_title_name($cer->field_certificate_target_id)] .= get_job_title_name($cer->field_certificate_target_id);
        }
        foreach ($certi as $certificate) {
          $output[$data['asset_id']][$data['job_title_id']]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
        }

        foreach ($job_user as $user) {
          $row = [];
          $certificate_list = '';
          $certificate_list = list_certificate($user->uid);
          $user_certificate = certificate_expiration_date($user->uid);
          $row[0] = $user->name;
          $row_key = 1;
          foreach ($certificate_name as $certi) {
            if (!empty($certi)) {
              if (in_array($certi, $certificate_list)) {
                $color = '';
                $field_expiry_date = strtotime($user_certificate[$certi]);
                $current_date = new DrupalDateTime();
                $current_date = strtotime($current_date);

                if ($field_expiry_date > $current_date) {
                  $diff = abs($field_expiry_date - $current_date);
                  $years = floor($diff / (365 * 60 * 60 * 24));
                  $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                  $row[$row_key]['date'] .= date('d-m-Y', strtotime($user_certificate[$certi]));
                }
                else {
                  $row[$row_key]['date'] .= date('d-m-Y', strtotime($user_certificate[$certi]));
                }
                $row_key++;
              }
              else {
                $row_key++;
              }
            }
          }
          $output[$data['asset_id']][$data['job_title_id']]['user'][$user->uid] = $row;
        }
        if (empty($row)) {
          $output['data'] = 'There is no Employee related to selected Asset and Job Title. Please try with other Job Title or Asset.';
        }
      }

      $response = [$output];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

}
