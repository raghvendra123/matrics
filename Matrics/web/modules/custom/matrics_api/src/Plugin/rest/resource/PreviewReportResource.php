<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Preview Report Resource.
 *
 * @RestResource(
 *   id = "preview_report",
 *   label = @Translation("Preview Report"),
 *   uri_paths = {
 *     "create" = "/preview_report"
 *   }
 * )
 */
class PreviewReportResource extends ResourceBase {

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
    $response = ['message' => t('Please check url')];
    $code = 400;
    if ($data) {
      if (empty($data['title'])) {
      }
      $datatype = $data['field_data_type'];
      $header = [
        'Employee Name',
        'Certificate link',
        'Certificate type',
        'Certificate start date',
        'Certificate expiry date',
      ];

      // Find and load all of the Article nodes we are going to include.
      $connection = \Drupal::database();
      $query = \Drupal::database()->select('users_field_data', 'u');
      $query->fields('u', ['uid', 'name']);
      $query->condition('u.uid', '1', '!=');
      if (array_key_exists('employee_name', $datatype)) {
        $employees = $data['field_data_type']['employee_name'];
        if (!empty($employees)) {
          $query->condition('u.uid', $employees, 'IN');
        }
      }
      if (array_key_exists('participant_name', $datatype)) {
        $u_id = $data['field_data_type']['participant_name'];
        if ($u_id != NULL) {
          $query->condition('u.uid', $u_id, '=');
        }
        if (!array_key_exists('course', $datatype)) {
          $datatype[] = 'Course';
        }
      }
      if (array_key_exists('asset', $datatype)) {
        $query->join('user__field_asset', 'fa', 'fa.entity_id = u.uid');
        $query->fields('fa', ['field_asset_target_id']);
        $query->join('node_field_data', 'n', 'n.nid = fa.field_asset_target_id');
        $query->fields('n', ['title']);
        $ass[] = $data['field_data_type']['asset'];
        if (!empty($ass)) {
          $query->condition('n.nid', $ass, 'IN');
        }
        array_push($header, "Asset");
      }
      if (array_key_exists('job_title', $datatype)) {
        $query->join('user__field_job_title', 'jt', 'jt.entity_id = u.uid');
        $query->fields('jt', ['field_job_title_target_id']);
        $job_title = $data['field_data_type']['job_title'];
        if (!empty($job_title)) {
          $query->condition('jt.field_job_title_target_id', $job_title, 'IN');
        }
        array_push($header, "Job title");
      }
      if (array_key_exists('certificate', $datatype)) {
        $query->join('user__field_certificates', 'c', 'c.entity_id = u.uid');
        $query->fields('c', ['field_certificates_target_id']);
        $query->join('paragraph_revision__field_certificate_type', 'ct', 'ct.entity_id = c.field_certificates_target_id');
        $query->fields('ct', ['field_certificate_type_target_id']);
        $certificate = $data['field_data_type']['certificate'];
        if (!empty($certificate)) {
          $query->condition('ct.field_certificate_type_target_id', $certificate, 'IN');
        }
      }
      if (array_key_exists('training_gap_score', $datatype)) {
        $query->join('user__field_training_gap_score', 'gs', 'gs.entity_id = u.uid');
        $query->fields('gs', ['field_training_gap_score_value']);
        $training_gap_score = $data['field_data_type']['training_gap_score'];
        if (!empty($training_gap_score)) {
          if ($training_gap_score == '< 50') {
            $query->condition('gs.field_training_gap_score_value', '50', '<');
          }
          elseif ($training_gap_score == '> 50') {
            $query->condition('gs.field_training_gap_score_value', '50', '>');
          }
          else {
            $query->condition('gs.field_training_gap_score_value', $training_gap_score, '=');
          }
        }
        array_push($header, "Training gap score");
      }
      if (array_key_exists('training_gap_count', $datatype)) {
        $query->join('user__field_training_gap_count', 'gc', 'gc.entity_id = u.uid');
        $query->fields('gc', ['field_training_gap_count_value']);
        $field_training_gapcount = $data['field_data_type']['training_gap_count'];
        if (!empty($field_training_gapcount)) {
          if ($field_training_gapcount == '< 50') {
            $query->condition('gc.field_training_gap_count_value', '50', '<');
          }
          elseif ($field_training_gapcount == '> 50') {
            $query->condition('gc.field_training_gap_count_value', '50', '>');
          }
          else {
            $query->condition('gc.field_training_gap_count_value', $field_training_gapcount, '=');
          }
        }
        array_push($header, "Training gap count");
      }
      if (array_key_exists('date_range', $datatype)) {
        $field_start_date = $data['field_data_type']['date_range']['start_date'];
        $field_expire_date = $data['field_data_type']['date_range']['expiry_date'];
        if ($field_start_date != NULL && $field_expire_date != NULL) {
          $start_date = $field_start_date;
          $expire_date = $field_expire_date;
          if (!array_key_exists('certificate', $datatype)) {
            $query->join('user__field_certificates', 'c', 'c.entity_id = u.uid');
            $query->fields('c', ['field_certificates_target_id']);
          }
          $query->join('paragraph_revision__field_start_date', 'sd', 'sd.entity_id = c.field_certificates_target_id');
          $query->fields('sd', ['field_start_date_value']);
          $query->join('paragraph_revision__field_expiry_date', 'ed', 'ed.entity_id = c.field_certificates_target_id');
          $query->fields('ed', ['field_expiry_date_value']);
          $query->condition('sd.field_start_date_value', $start_date, '>=');
          $query->condition('ed.field_expiry_date_value', $expire_date, '<=');
        }
        // array_push($header,"Date range");
      }
      if (array_key_exists('course', $datatype)) {
        $query->join('user__field_course_details', 'crs', 'crs.entity_id = u.uid');
        $query->fields('crs', ['field_course_details_target_id']);
        $query->join('paragraph_revision__field_course_', 'p_crs', 'p_crs.entity_id = crs.field_course_details_target_id');
        $query->fields('p_crs', ['field_course__target_id']);
        $course = $data['field_data_type']['course'];
        if (!empty($course)) {
          $query->condition('p_crs.field_course__target_id', $course, 'IN');
        }
        array_push($header, "Course");
      }
      if (array_key_exists('training_request_number', $datatype)) {
        if (!array_key_exists('course', $datatype)) {
          $query->join('user__field_course_details', 'crs', 'crs.entity_id = u.uid');
          // $query->fields('crs', ['field_course_details_target_id']);
          $query->join('paragraph_revision__field_course_', 'p_crs', 'p_crs.entity_id = crs.field_course_details_target_id');
          $query->fields('p_crs', ['field_course__target_id']);
        }
        $query->join('node__field_course_id', 'n_crs', 'n_crs.entity_id = p_crs.field_course__target_id');
        $query->fields('n_crs', ['field_course_id_value']);
        $query->join('node_field_data', 'n_data', 'n_data.nid = p_crs.field_course__target_id');
        $query->addField('n_data', 'title', 'course_name');
        $course_id = $data['field_data_type']['training_request_number'];
        if (!empty($course_id)) {
          $query->condition('n_crs.field_course_id_value', $course_id, 'IN');
        }
        array_push($header, "Course ID");
        array_push($header, "Course Name");
      }
      if (array_key_exists('training_provider', $datatype)) {
        if (!array_key_exists('course', $datatype)) {
          $query->join('user__field_course_details', 'crs', 'crs.entity_id = u.uid');
          $query->join('paragraph__field_course_', 'p_crs', 'p_crs.entity_id = crs.field_course_details_target_id');
          $query->fields('p_crs', ['field_course__target_id']);
        }
        $query->join('node__field_training_provider', 'n_trp', 'n_trp.entity_id = p_crs.field_course__target_id');
        $query->fields('n_trp', ['field_training_provider_target_id']);
        $query->join('node_field_data', 'n_trp_data', 'n_trp_data.nid = n_trp.field_training_provider_target_id');
        $query->addField('n_trp_data', 'title', 'training_provider');
        $training_providers = $data['field_data_type']['training_provider'];
        if (!empty($training_providers)) {
          $query->condition('n_trp.field_training_provider_target_id', $training_providers, 'IN');
        }
        array_push($header, "Training provider");
      }

      $result = $query->execute()->fetchAll();

      foreach ($result as $key => $node) {
        if ((array_key_exists('date_range', $datatype) && $field_start_date != NULL && $field_expire_date != NULL) || array_key_exists('field_certificate', $datatype)) {
          $certificate = get_certificate_new($node->uid, $node->field_certificates_target_id);
        }
        else {
          $certificate = get_certificate($node->uid);
        }
        $data1[$key]['name'] = $node->name;
        // $certificate = get_certificate($node->uid);
        $data1[$key]['link'] = $certificate['link'];
        $data1[$key]['type'] = $certificate['type'];
        $data1[$key]['start_date'] = $certificate['start_date'];
        $data1[$key]['expiry_date'] = $certificate['expiry_date'];

        if (isset($node->title)) {
          $data1[$key]['title'] = $node->title;
        }
        if (isset($node->field_job_title_target_id)) {
          $data1[$key]['job_title'] = get_job_title_name($node->field_job_title_target_id);
        }
        if (isset($node->field_training_gap_score_value)) {
          $data1[$key]['training_gap_score'] = $node->field_training_gap_score_value;
        }
        if (isset($node->field_training_gap_count_value)) {
          $data1[$key]['training_gap_count'] = $node->field_training_gap_count_value;
        }
        if (isset($node->field_course_details_target_id)) {
          $course = get_course($node->uid, $node->field_course_details_target_id);
          $data1[$key]['course_details'] = $course['title'];
        }
        if (isset($node->field_course_id_value)) {
          $data1[$key]['course_id'] = $node->field_course_id_value;
          $data1[$key]['course_name'] = $node->course_name;
        }
        if (isset($node->training_provider)) {
          $data1[$key]['training_provider'] = $node->training_provider;
        }
      }
      $response = [$header, $data1];
      $code = 200;
    }
    return new ModifiedResourceResponse($response, $code);
  }

}
