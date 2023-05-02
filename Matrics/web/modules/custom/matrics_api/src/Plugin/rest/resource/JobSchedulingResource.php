<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a Job Scheduling Resource.
 *
 * @RestResource(
 *   id = "job_scheduling",
 *   label = @Translation("Job Scheduling"),
 *   uri_paths = {
 *     "canonical" = "/matrics_api/job_scheduling/{job}/{uid}/{asset_id}",
 *     "create" = "/matrics_api/job_scheduling"
 *   }
 * )
 */
class JobSchedulingResource extends ResourceBase {

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
  public function get($job = NULL, $uid = NULL, $asset_id = NULL) {
    $response = ['message' => t('Please check url')];
    $code = 400;
    if ($job != NULL && $uid != NULL && $asset_id != NULL) {
      $connection = \Drupal::database();
      $query = \Drupal::database()->select('users_field_data', 'u');
      $query->fields('u', ['uid', 'name']);
      $query->condition('u.uid', '1', '!=');
      $query->condition('u.uid', $uid, '!=');
      $query->join('user__field_job_title', 'jt', 'jt.entity_id = u.uid');
      $query->fields('jt', ['entity_id']);
      $query->condition('jt.field_job_title_target_id', $job);
      $query->join('user__field_employee_status', 'fa', 'fa.entity_id = u.uid');
      $query->condition('fa.field_employee_status_value', 2, '=');
      $query->fields('fa', ['entity_id']);

      $result = $query->execute()->fetchAll();
      foreach ($result as $value) {
        $option[$value->uid] = $value->name;
      }
      $swap_user = $uid;
      $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
        ->fields('jt', ['entity_id'])
        ->condition('jt.field_job_title_target_id', $job);
      $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
      $query2->fields('u', ['name', 'uid']);
      $query2->join('user__field_training_gap_score', 't', 'u.uid = t.entity_id');
      $query2->fields('t', ['field_training_gap_score_value']);
      $query2->join('user__field_employee_status', 'fa', 'fa.entity_id = u.uid');
      $query2->condition('fa.field_employee_status_value', 2, '=');

      $job_user = $query2->execute()->fetchAll();
      $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
      $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
      $query3->condition('fc.entity_id', $job);
      $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
      $query3->fields('n', ['title']);

      $certi = $query3->execute()->fetchAll();
      $node = Node::load($job);

      $output[$job]['job_title'] = $node->getTitle();
      $certificate_name = [];
      $certificate_name['0'] = '';
      foreach ($certi as $cer) {
        $certificate_name[get_job_title_name($cer->field_certificate_target_id)] .= get_job_title_name($cer->field_certificate_target_id);
      }
      foreach ($certi as $certificate) {
        $output[$job]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
      }

      foreach ($job_user as $user) {
        $location = \Drupal::database()->select('user__field_location', 'l')
          ->fields('l', ['entity_id', 'field_location_locality'])
          ->condition('l.entity_id', $user->uid);
        $location = $location->execute()->fetchAll();

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

                if ($months >= 6 || $years != 0) {
                  $color = '#C6EFCE';
                }
                elseif ($months <= 6 && $months >= 3) {
                  $color = '#FFCE33';
                }
                elseif ($months <= 3 && $months >= 1) {
                  $color = '#FFEB9C';
                }
                else {
                  $color = '#FFC7CE';
                }
                $row[$row_key]['date'] .= $user_certificate[$certi];
                $row[$row_key]['color'] .= $color;
                $row[$row_key]['cname'] = $certi;
              }
              $row_key++;
            }
            else {
              $row[$row_key]['date'] .= '0';
              $row[$row_key]['color'] .= '#FFC7CE';
              $row[$row_key]['cname'] = $certi;
              $row_key++;
            }
          }
        }
        $output[$job]['user']['user_data'][$user->uid] = $row;
        $output[$job]['user']['user_score'][$user->uid] = $user->field_training_gap_score_value;
        $output[$job]['user']['user_location'][$user->uid] = $location[0]->field_location_locality;
        $output[$job]['user']['swap_user'][$user->uid] = $swap_user;
        $output[$job]['user']['asset'][$user->uid] = $asset_id;
      }
      $response = [$option, $output];
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
    $response = ['message' => $this->t('Please Post user id key.')];
    $code = 400;
    if (!empty($data['uid'])) {
      $job = $data['job'];
      $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
        ->fields('jt', ['entity_id'])
        ->condition('jt.field_job_title_target_id', $job);
      $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
      $query2->fields('u', ['name', 'uid']);
      $query2->join('user__field_training_gap_score', 't', 'u.uid = t.entity_id');
      $query2->fields('t', ['field_training_gap_score_value']);
      $query2->join('user__field_employee_status', 'fa', 'fa.entity_id = u.uid');
      $query2->condition('fa.field_employee_status_value', 2, '=');
      if ($selectedValue = $data['uid']) {
        $query2->condition('u.uid', $selectedValue);
      }
      $job_user = $query2->execute()->fetchAll();
      $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
      $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
      $query3->condition('fc.entity_id', $job);
      $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
      $query3->fields('n', ['title']);

      $certi = $query3->execute()->fetchAll();
      $node = Node::load($job);

      $output[$job]['job_title'] = $node->getTitle();
      $certificate_name = [];
      $certificate_name['0'] = '';
      foreach ($certi as $cer) {
        $certificate_name[get_job_title_name($cer->field_certificate_target_id)] .= get_job_title_name($cer->field_certificate_target_id);
      }
      foreach ($certi as $certificate) {
        $output[$job]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
      }

      foreach ($job_user as $user) {
        $location = \Drupal::database()->select('user__field_location', 'l')
          ->fields('l', ['entity_id', 'field_location_locality'])
          ->condition('l.entity_id', $user->uid);
        $location = $location->execute()->fetchAll();

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

                if ($months >= 6 || $years != 0) {
                  $color = '#C6EFCE';
                }
                elseif ($months <= 6 && $months >= 3) {
                  $color = '#FFCE33';
                }
                elseif ($months <= 3 && $months >= 1) {
                  $color = '#FFEB9C';
                }
                else {
                  $color = '#FFC7CE';
                }
                $row[$row_key]['date'] .= $user_certificate[$certi];
                $row[$row_key]['color'] .= $color;
                $row[$row_key]['cname'] = $certi;

              }
              $row_key++;
            }
            else {
              $row[$row_key]['date'] .= '0';
              $row[$row_key]['color'] .= '#FFC7CE';
              $row[$row_key]['cname'] = $certi;
              $row_key++;
            }
          }
        }
        $output[$job]['user']['user_data'][$user->uid] = $row;
        $output[$job]['user']['user_score'][$user->uid] = $user->field_training_gap_score_value;
        $output[$job]['user']['user_location'][$user->uid] = $location[0]->field_location_locality;
        $output[$job]['user']['swap_user'][$user->uid] = $data['swap_user_id'];
        $output[$job]['user']['asset'][$user->uid] = $data['asset_id'];
      }
      $response = [$output];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

}
