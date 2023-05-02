<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a Preview Reports Resource.
 *
 * @RestResource(
 *   id = "save_report",
 *   label = @Translation("Save Report"),
 *   uri_paths = {
 *     "create" = "/save_report"
 *   }
 * )
 */
class SaveReportResource extends ResourceBase {

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
   * Responds to POST requests with asset.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $response = ['message' => t('Please check url')];
    $code = 400;
    $handle = fopen('php://temp', 'w');
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
      $field_dt_type = [];

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
        array_push($field_dt_type, "Employee Name");
      }
      if (array_key_exists('participant_name', $datatype)) {
        $u_id = $data['field_data_type']['participant_name'];
        if ($u_id != NULL) {
          $query->condition('u.uid', $u_id, '=');
        }
        if (!array_key_exists('course', $datatype)) {
          $datatype[] = 'course';
        }
        array_push($field_dt_type, "Participant name");
      }
      if (array_key_exists('asset', $datatype)) {
        $query->join('user__field_asset', 'fa', 'fa.entity_id = u.uid');
        $query->fields('fa', ['field_asset_target_id']);
        $query->join('node_field_data', 'n', 'n.nid = fa.field_asset_target_id');
        $query->fields('n', ['title']);
        $ass = $data['field_data_type']['asset'];
        if (!empty($ass)) {
          $query->condition('n.nid', $ass, 'IN');
        }
        array_push($header, "Asset");
        array_push($field_dt_type, "Asset");
      }
      if (array_key_exists('job_title', $datatype)) {
        $query->join('user__field_job_title', 'jt', 'jt.entity_id = u.uid');
        $query->fields('jt', ['field_job_title_target_id']);
        $job_title = $data['field_data_type']['job_title'];
        if (!empty($job_title)) {
          $query->condition('jt.field_job_title_target_id', $job_title, 'IN');
        }
        array_push($field_dt_type, "Job title");
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
        array_push($field_dt_type, "Certificates");
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
        array_push($field_dt_type, "Training gap score");
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
        array_push($field_dt_type, "Training gap count");
        array_push($header, "Training gap count");
      }
      if (array_key_exists('date_range', $datatype)) {
        $field_start_date = $data['field_data_type']['date_range']['start_date'];
        $field_expire_date = $data['field_data_type']['date_range']['expiry_date'];
        if ($field_start_date != NULL && $field_expire_date != NULL) {
          $start_date = date("Y-m-d", strtotime($field_start_date));
          $expire_date = date("Y-m-d", strtotime($field_expire_date));
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
        array_push($field_dt_type, "Date range");
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
        array_push($field_dt_type, "Course");
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
        array_push($field_dt_type, "Training request number");
      }
      if (array_key_exists('training_provider', $datatype)) {
        if (!array_key_exists('course', $datatype)) {
          $query->join('user__field_course_details', 'crs', 'crs.entity_id = u.uid');
          // $query->fields('crs', ['field_course_details_target_id']);
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
        array_push($field_dt_type, "Training provider");
      }

      $result = $query->execute()->fetchAll();

      // Add the header as the first line of the CSV.
      fputcsv($handle, $header);

      foreach ($result as $key => $node) {
        if ((array_key_exists('date_range', $datatype) && $field_start_date != NULL && $field_expire_date != NULL) || array_key_exists('certificate', $datatype)) {
          $certificate = get_certificate_new($node->uid, $node->field_certificates_target_id);
        }
        else {
          $certificate = get_certificate($node->uid);
        }
        $data1['name'] = $node->name;
        // $certificate = get_certificate($node->uid);
        $data1['link'] = $certificate['link'];
        $data1['ctype'] = $certificate['type'];
        $data1['start_date'] = $certificate['start_date'];
        $data1['expiry_date'] = $certificate['expiry_date'];

        if (isset($node->title)) {
          $data1['asset'] = $node->title;
        }
        if (isset($node->field_job_title_target_id)) {
          $data1['job_title'] = get_job_title_name($node->field_job_title_target_id);
        }
        if (isset($node->field_training_gap_score_value)) {
          $data1['training_gap_score'] = $node->field_training_gap_score_value;
        }
        if (isset($node->field_training_gap_count_value)) {
          $data1['training_gap_count'] = $node->field_training_gap_count_value;
        }
        if (isset($node->field_course_details_target_id)) {
          $course = get_course($node->uid, $node->field_course_details_target_id);
          $data1['course'] = $course['title'];
        }
        if (isset($node->field_course_id_value)) {
          $data1['course_id'] = $node->field_course_id_value;
          $data1['course'] = $node->course_name;
        }
        if (isset($node->training_provider)) {
          $data1['training_provider'] = $node->training_provider;
        }
        // Add the data we exported to the next line of the CSV>.
        fputcsv($handle, array_values($data1));
      }

      // Reset where we are in the CSV.
      rewind($handle);

      // Retrieve the data from the file handler.
      $csv_data = stream_get_contents($handle);
      // Close the file handler since we don't need it anymore.  We are not storing
      // this file anywhere in the filesystem.
      fclose($handle);

      // This is the "magic" part of the code.  Once the data is built, we can
      // return it as a response.
      $response = new Response();
      $filename = strtolower(str_replace(' ', '-', $data['title'])) . '.csv';

      // By setting these 2 header options, the browser will see the URL
      // used by this Controller to return a CSV file called "article-report.csv".
      $response->headers->set('Content-Type', 'text/csv');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

      // $image = base64_decode($csv_data);
      $file = file_save_data($csv_data, 'public://report/' . $filename);

      // dump($data['field_data_type']['date_range']['start_date'], $data['field_data_type']['date_range']['expiry_date']);
      // dump(date("d/m/Y", strtotime($data['field_data_type']['date_range']['start_date'])), date("d/m/Y", strtotime($data['field_data_type']['date_range']['expiry_date'])));die();
      $node = Node::create([
        'type' => 'reports',
        'title' => $data['title'],
        'field_file' => $file->id(),
        'field_data_type' => $field_dt_type,
        'field_asset' => $data['field_data_type']['asset'],
        'field_employee_name' => $data['field_data_type']['employee_name'],
        'field_certificate' => $data['field_data_type']['certificate'],
        'field_job_title' => $data['field_data_type']['job_title'],
        'field_training_gap' => $data['field_data_type']['training_gap_score'],
        'field_training_gapcount' => $data['field_data_type']['training_gap_count'],
        'field_course' => $data['field_data_type']['course'],
        'field_start_date' => date("Y-m-d", strtotime($data['field_data_type']['date_range']['start_date'])),
        'field_expir' => date("Y-m-d", strtotime($data['field_data_type']['date_range']['expiry_date'])),
        'field_participant_name' => $data['field_data_type']['participant_name'],
        'field_training_request_number' => $data['field_data_type']['training_request_number'],
        'field_training_provider' => $data['field_data_type']['training_provider'],
      ]);

      $node->save();
      $response = ['message' => 'You can Download the Report from Report Listing'];
      $code = 200;
    }
    return new ModifiedResourceResponse($response, $code);
  }

}
