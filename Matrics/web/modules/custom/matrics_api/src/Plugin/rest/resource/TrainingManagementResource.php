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
 * Provides a Training Management Resource.
 *
 * @RestResource(
 *   id = "training_management",
 *   label = @Translation("Training Management"),
 *   uri_paths = {
 *     "canonical" = "/training_management",
 *     "create" = "/training_management"
 *   }
 * )
 */
class TrainingManagementResource extends ResourceBase {

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
    $connection = \Drupal::database();
    $query = $connection->select('user__field_certificates ', 'c');
    $query->fields('c', ['entity_id', 'field_certificates_target_id']);
    $query->join('paragraph__field_expiry_date', 'd', 'd.entity_id = c.field_certificates_target_id');
    $query->fields('d', ['field_expiry_date_value']);
    $user_certificate = $query->execute()->fetchAll();

    $connection = \Drupal::database();
    $query = $connection->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'title']);
    $query->condition('n.type', 'asset');

    $result = $query->execute()->fetchAll();
    $six_plus = 0;
    $six_to_three = 0;
    $three_to_one = 0;
    $one_month_or_expired = 0;

    foreach ($result as $key => $value) {
      $asset_id = $value->nid;
      $query1 = \Drupal::database()->select('node__field_asset', 'fa')->fields('fa', ['entity_id'])->condition('fa.field_asset_target_id', $asset_id)->condition('fa.bundle', 'job_title');
      $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
      $query1->fields('n', ['title']);
      $job_title = $query1->execute()->fetchAll();

      foreach ($job_title as $job) {
        $query2 = \Drupal::database()->select('user__field_job_title', 'jt')->fields('jt', ['entity_id'])->condition('jt.field_job_title_target_id', $job->entity_id);
        $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
        $query2->fields('u', ['name', 'uid']);
        $query2->join('user__roles', 'r', 'u.uid = r.entity_id');
        $query2->condition('r.roles_target_id', 'individual_employee');
        $job_user = $query2->execute()->fetchAll();
        $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
        $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
        $query3->condition('fc.entity_id', $job->entity_id);
        $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
        $query3->fields('n', ['title']);
        $certi = $query3->execute()->fetchAll();
        $certificate_name = [];
        $certificate_name['0'] = '';
        foreach ($certi as $cer) {
          $certificate_name[get_job_title_name($cer->field_certificate_target_id)] = get_job_title_name($cer->field_certificate_target_id);
        }
        foreach ($job_user as $user) {
          $row = [];
          $certificate_list = '';
          $certificate_list = list_certificate($user->uid);
          $user_certificate = certificate_expiration_date($user->uid);
          $current_date = new DrupalDateTime();
          $current_date = strtotime($current_date);
          $row_key = 1;
          foreach ($certificate_name as $certi) {
            if (!empty($certi)) {
              if (in_array($certi, $certificate_list)) {
                $color = '';
                $field_expiry_date = isset($user_certificate[$certi]) ? strtotime($user_certificate[$certi]) : 0;
                $query = $connection->select('user__field_course_details', 'c');
                $query->fields('c', ['field_course_details_target_id']);
                $query->condition('c.entity_id', $user->uid);
                $query->join('paragraph__field_status', 'n', 'n.entity_id = c.field_course_details_target_id');
                $query->condition('n.field_status_value', [
                  'completed',
                  'not required',
                ], 'IN');
                $user_course = $query->execute()->fetchAll();

                $user_course_booking = [];
                if (!empty($user_course)) {
                  $query = $connection->select('paragraph__field_close_booking', 'd');
                  $query->fields('d', ['field_close_booking_value']);
                  $query->condition('d.field_close_booking_value', '0');
                  $query->condition('d.entity_id', $user_course[0]->field_course_details_target_id);
                  $user_course_booking = $query->execute()->fetchAll();
                }
                else {
                  $user_course_booking = ['no course'];
                }
                if (!empty($field_expiry_date) && !empty($user_course_booking)) {
                  $diff = abs($field_expiry_date - $current_date);
                  $years = round($diff / 60 / 60 / 24 / 365);
                  $months = round($diff / 60 / 60 / 24 / 30);
                  $start_time = isset($user_certificate[$certi . '2']) ? new DrupalDateTime($user_certificate[$certi . '2']) : 0;
                  $start_timestamp = $start_time->getTimestamp();
                  $expirty_time = isset($user_certificate[$certi]) ? new DrupalDateTime($user_certificate[$certi]) : 0;
                  $expiry_timestamp = $expirty_time->getTimestamp();
                  $c_date = strtotime(date('y-m-d 00:00:00'));
                  $c_days = (($expiry_timestamp - $c_date) / (1000 * 3600 * 24)) * 1000;
                  $count_days = $current_date < $field_expiry_date ? $c_days : 0;
                  $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                  $row[$row_key]['name'] = get_first_name($user->uid);
                  $row[$row_key]['last_name'] = get_last_name($user->uid);
                  $row[$row_key]['asset'] = $value->title;
                  $row[$row_key]['job_title'] = $job->title;
                  $row[$row_key]['uid'] = $user->uid;
                  $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                  $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                  $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                  $row[$row_key]['course_status'] = get_course_status($user->uid, $user_certificate[$certi . '5']);
                  $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                  $row[$row_key]['cert'] = $certi;
                  $row[$row_key]['month'] = $months;
                  $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                  $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                  $row[$row_key]['current_date'] = $current_date;
                  $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                  $output[$user->uid] = $row;
                }
                $row_key++;
              }
            }
          }
        }
      }
    }
    $response = ['data' => $output];

    return new ResourceResponse($response);
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
    $response = ['message' => $this->t('Please Post any key of Expiry type, Asset, Job title, Country, Start date, End date, Training Provider, Employee, Accreditation, Course Booked, Certificate Start date, Certificate End date.')];
    if (!empty($data)) {
      $certificate_count = NULL;
      if (isset($data['certificate_count']) && !empty($data['certificate_count'])) {
        $certificate_count = $data['certificate_count'];
      }
      $asset_id = NULL;
      if (isset($data['asset_id']) && !empty($data['asset_id'])) {
        $asset_id = $data['asset_id'];
      }
      $job_title_id = NULL;
      if (isset($data['job_title_id']) && !empty($data['job_title_id'])) {
        $job_title_id = $data['job_title_id'];
      }
      $start_date1 = NULL;
      if (isset($data['start_date']) && !empty($data['start_date'])) {
        $start_date1 = strtotime($data['start_date']);
      }
      $end_date1 = NULL;
      if (isset($data['end_date']) && !empty($data['end_date'])) {
        $end_date1 = strtotime($data['end_date']);
      }
      $training_provider = NULL;
      if (isset($data['training_provider']) && !empty($data['training_provider'])) {
        $training_provider = $data['training_provider'];
      }
      $employee = NULL;
      if (isset($data['employee']) && !empty($data['employee'])) {
        $employee = $data['employee'];
      }
      $accreditation = NULL;
      if (isset($data['accreditation']) && !empty($data['accreditation'])) {
        $accreditation = $data['accreditation'];
      }
      $course_booked = NULL;
      if (isset($data['course_booked']) && !empty($data['course_booked'])) {
        $course_booked = $data['course_booked'];
      }
      $cer_start_date1 = NULL;
      if (isset($data['cer_start_date']) && !empty($data['cer_start_date'])) {
        $cer_start_date1 = strtotime($data['cer_start_date']);
      }
      $cer_end_date1 = NULL;
      if (isset($data['cer_end_date']) && !empty($data['cer_end_date'])) {
        $cer_end_date1 = strtotime($data['cer_end_date']);
      }

      $connection = \Drupal::database();
      $query = $connection->select('user__field_certificates ', 'c');
      $query->fields('c', ['entity_id', 'field_certificates_target_id']);
      $query->join('paragraph__field_expiry_date', 'd', 'd.entity_id = c.field_certificates_target_id');
      $query->fields('d', ['field_expiry_date_value']);

      $user_certificate = $query->execute()->fetchAll();
      $connection = \Drupal::database();
      $query = $connection->select('node_field_data', 'n');
      $query->fields('n', ['nid', 'title']);
      $query->condition('n.type', 'asset');
      if (!empty($asset_id)) {
        $query->condition('n.nid', $asset_id, 'IN');
      }

      $result = $query->execute()->fetchAll();
      $six_plus = 0;
      $six_to_three = 0;
      $three_to_one = 0;
      $one_month_or_expired = 0;
      $output = [];

      foreach ($result as $key => $value) {
        $asset_id = $value->nid;
        $query1 = \Drupal::database()->select('node__field_asset', 'fa')->fields('fa', ['entity_id'])->condition('fa.field_asset_target_id', $asset_id)->condition('fa.bundle', 'job_title');
        $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
        if (!empty($job_title_id)) {
          if (!in_array("all", $job_title_id)) {
            $query1->condition('n.nid', $job_title_id, 'IN');
          }
        }
        $query1->fields('n', ['title']);
        $job_title = $query1->execute()->fetchAll();

        foreach ($job_title as $job) {
          $query2 = \Drupal::database()->select('user__field_job_title', 'jt')->fields('jt', ['entity_id'])->condition('jt.field_job_title_target_id', $job->entity_id);
          $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
          $query2->fields('u', ['name', 'uid']);
          $query2->join('user__roles', 'r', 'u.uid = r.entity_id');
          $query2->condition('r.roles_target_id', 'individual_employee');
          if (!empty($employee)) {
            $query2->join('user__field_employee_name', 'f', 'u.uid = f.entity_id');
            $query2->fields('f', ['field_employee_name_value']);
            $query2->condition('f.entity_id', $employee, 'IN');
          }

          if (!empty($training_provider) || !empty($accreditation) || !empty($course_booked)) {
            $query2->join('user__field_certificates', 'uc', 'uc.entity_id = u.uid');
            $query2->fields('uc', ['field_certificates_target_id']);
            $query2->orderBy('field_certificates_target_id', 'DESC');
            $query2->join('paragraph__field_enable', 'en', 'en.entity_id = uc.field_certificates_target_id');
            $query2->fields('en', ['field_enable_value']);
            $query2->condition('en.field_enable_value', 1);
            $query2->join('paragraph__field_certificate_type', 'pcy', 'pcy.entity_id = uc.field_certificates_target_id');
            $query2->fields('pcy', ['field_certificate_type_target_id']);
            $query2->join('node_field_data', 'nd', 'pcy.field_certificate_type_target_id = nd.nid');
            $query2->addField('nd', 'title', 'certificate_type');
            $query2->join('paragraph__field_course_name', 'pc', 'pc.entity_id = uc.field_certificates_target_id');
            $query2->fields('pc', ['field_course_name_target_id']);
            $query2->join('paragraph__field_provider_name', 'fa', 'fa.entity_id = uc.field_certificates_target_id');
            $query2->fields('fa', ['field_provider_name_target_id']);
            if (!empty($training_provider)) {
              $query2->join('node_field_data', 'nai', 'fa.field_provider_name_target_id = nai.nid');
              $query2->addField('nai', 'title', 'provider_title');
              $query2->condition('fa.field_provider_name_target_id', $training_provider, 'IN');
            }
            if (!empty($accreditation)) {
              // $query2->fields('pc', ['field_course_name_target_id']);
              $query2->join('node_field_data', 'n', 'n.nid = pc.field_course_name_target_id');
              $query2->fields('n', ['title']);
              $query2->join('node__field_provider_details', 'd', 'd.entity_id = n.nid');
              // $query2->fields('d', ['field_provider_details_target_id']);
              $query2->join('paragraph__field_provider_name', 'p', 'p.entity_id = d.field_provider_details_target_id');
              $query2->addField('p', 'field_provider_name_target_id', 'name');
              $query2->join('paragraph__field_accreditation', 'a', 'a.entity_id = d.field_provider_details_target_id');
              $query2->fields('a', ['field_accreditation_value']);
              $query2->condition('a.field_accreditation_value', $accreditation, 'IN');
            }
            if (!empty($form_state->getValue('course_booked') && $form_state->getValue('course_booked') != 'to be actioned')) {
              $query2->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
              $query2->fields('c', ['field_course_details_target_id']);
              $query2->join('paragraph__field_certificate_type', 'se', 'se.entity_id = c.field_course_details_target_id');
              $query2->fields('se', ['field_certificate_type_target_id']);
              // $query2->condition('f.title', 'Course 2');
              $query2->join('paragraph__field_status', 'ps', 'ps.entity_id = c.field_course_details_target_id');
              $query2->fields('ps', ['field_status_value']);
              $query2->condition('ps.field_status_value', $form_state->getValue('course_booked'));
            }
            if (!empty($form_state->getValue('course_booked')) && $form_state->getValue('course_booked') == 'to be actioned') {
              $query2->leftJoin('user__field_course_details', 'c', 'c.entity_id = u.uid');
              $query2->fields('c', ['field_course_details_target_id']);
              $query2->leftJoin('paragraph__field_certificate_type', 'se', 'se.entity_id = c.field_course_details_target_id');
              $query2->fields('se', ['field_certificate_type_target_id']);
            }
          }

          $job_user = $query2->execute()->fetchAll();
          $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
          $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
          $query3->condition('fc.entity_id', $job->entity_id);
          $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
          $query3->fields('n', ['title']);
          $certi = $query3->execute()->fetchAll();
          $certificate_name = [];
          $certificate_name['0'] = '';
          foreach ($certi as $cer) {
            $certificate_name[get_job_title_name($cer->field_certificate_target_id)] = get_job_title_name($cer->field_certificate_target_id);
          }
          $job_key = 1;
          foreach ($job_user as $user) {
            if (!empty($form_state->getValue('course_booked')) && ($form_state->getValue('course_booked') != 'to be actioned')) {
              if ($user->se_field_certificate_type_target_id != $user->field_certificate_type_target_id) {
                continue;
              }
            }
            elseif (!empty($form_state->getValue('course_booked')) && ($form_state->getValue('course_booked') == 'to be actioned')) {
              if ($user->se_field_certificate_type_target_id == $user->field_certificate_type_target_id) {
                continue;
              }
            }
            if (!empty($accreditation)) {
              if ($user->field_provider_name_target_id != $user->p_field_provider_name_target_id) {
                continue;
              }
            }
            $row = [];
            $certificate_list = '';
            $certificate_list = list_certificate($user->uid);
            $user_certificate = certificate_expiration_date($user->uid);
            $row_key = 1;
            foreach ($certificate_name as $certi) {
              if (!empty($certi)) {
                if (property_exists($user, 'field_status_value')) {
                  if (!empty($user->field_status_value)) {
                    $status = $user->field_status_value;
                  }
                  else {
                    $status = 'To be actioned';
                  }
                }
                else {
                  $status = isset($user_certificate[$certi . '5']) ? get_course_status($user->uid, $user_certificate[$certi . '5']) : 0;
                }
                if (property_exists($user, 'field_employee_name_value')) {
                  $username = $user->field_employee_name_value;
                }
                else {
                  $username = get_first_name($user->uid);
                }
                $current_date = new DrupalDateTime();
                $current_date = strtotime($current_date);
                $field_expiry_date = isset($user_certificate[$certi]) ? strtotime($user_certificate[$certi]) : 0;
                $field_start_date = isset($user_certificate[$certi . '2']) ? strtotime($user_certificate[$certi . '2']) : 0;
                $start_time = isset($user_certificate[$certi . '2']) ? new DrupalDateTime($user_certificate[$certi . '2']) : 0;
                $start_timestamp = !empty($start_time) ? $start_time->getTimestamp() : 0;
                $expirty_time = isset($user_certificate[$certi]) ? new DrupalDateTime($user_certificate[$certi]) : 0;
                $expiry_timestamp = !empty($expirty_time) ? $expirty_time->getTimestamp() : 0;
                $c_date = strtotime(date('y-m-d 00:00:00'));
                $c_days = (($expiry_timestamp - $c_date) / (1000 * 3600 * 24)) * 1000;
                $count_days = $current_date < $field_expiry_date ? $c_days : 0;
                $condition = in_array($certi, $certificate_list);
                if (!empty($training_provider) || !empty($accreditation) || !empty($course_booked)) {
                  $condition = in_array($certi, $certificate_list) && $user->certificate_type == $certi;
                }
                if ($condition) {
                  $query = $connection->select('user__field_course_details', 'c');
                  $query->fields('c', ['field_course_details_target_id']);
                  $query->condition('c.entity_id', $user->uid);
                  $query->join('paragraph__field_status', 'n', 'n.entity_id = c.field_course_details_target_id');
                  $query->condition('n.field_status_value', [
                    'completed',
                    'not required',
                  ], 'IN');
                  $user_course = $query->execute()->fetchAll();

                  $user_course_booking = [];
                  if (!empty($user_course)) {
                    $query = $connection->select('paragraph__field_close_booking', 'd');
                    $query->fields('d', ['field_close_booking_value']);
                    $query->condition('d.field_close_booking_value', '0');
                    $query->condition('d.entity_id', $user_course[0]->field_course_details_target_id);
                    $user_course_booking = $query->execute()->fetchAll();
                  }
                  else {
                    $user_course_booking = ['no course'];
                  }
                  $color = '';

                  if (!empty($start_date1)) {
                    $start_date = strtotime($start_date1);
                  }
                  if (!empty($end_date1)) {
                    $end_date = strtotime($end_date1);
                  }
                  if (!empty($cer_start_date1)) {
                    $cer_start_date = strtotime($cer_start_date1);
                  }
                  if (!empty($cer_end_date1)) {
                    $cer_end_date = strtotime($cer_end_date1);
                  }
                  $diff = abs($field_expiry_date - $current_date);
                  $years = round($diff / 60 / 60 / 24 / 365);
                  $months = round($diff / 60 / 60 / 24 / 30);
                  if (!empty($user_course_booking)) {
                    if (!empty($start_date1) && !empty($end_date1) && !empty($cer_start_date1) && !empty($cer_end_date1)) {
                      if ($field_expiry_date > $start_date && $field_expiry_date < $end_date && $field_start_date > $cer_start_date && $field_start_date < $cer_end_date) {

                        if (!empty($certificate_count)) {
                          $cer_count = $certificate_count;
                          if (in_array('expired', $cer_count)) {
                            if ($field_expiry_date < $current_date && !empty($field_expiry_date)) {
                              $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                              $row[$row_key]['name'] = $username;
                              $row[$row_key]['last_name'] = get_last_name($user->uid);
                              $row[$row_key]['asset'] = $value->title;
                              $row[$row_key]['job_title'] = $job->title;
                              $row[$row_key]['uid'] = $user->uid;
                              $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                              $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                              $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                              $row[$row_key]['course_status'] = $status;
                              $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                              $row[$row_key]['cert'] = $certi;
                              $row[$row_key]['month'] = $months;
                              $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                              $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                              $row[$row_key]['current_date'] = $current_date;
                              $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                              $output[$user->uid . $job_key] = $row;
                            }
                          }
                          if (in_array('three_to_one', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 3 && $months > 1);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                          if (in_array('six_to_three', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 6 && $months > 3);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                          if (in_array('one_month', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 1);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                        }
                        else {
                          if (!empty($field_expiry_date)) {
                            $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                            $row[$row_key]['name'] = $username;
                            $row[$row_key]['last_name'] = get_last_name($user->uid);
                            $row[$row_key]['asset'] = $value->title;
                            $row[$row_key]['job_title'] = $job->title;
                            $row[$row_key]['uid'] = $user->uid;
                            $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                            $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                            $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                            $row[$row_key]['course_status'] = $status;
                            $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                            $row[$row_key]['cert'] = $certi;
                            $row[$row_key]['month'] = $months;
                            $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                            $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                            $row[$row_key]['current_date'] = $current_date;
                            $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                            $output[$user->uid . $job_key] = $row;
                          }
                        }
                      }
                      else {
                      }
                    }
                    elseif (!empty($start_date1) && !empty($end_date1)) {
                      if ($field_expiry_date > $start_date && $field_expiry_date < $end_date) {
                        if (!empty($certificate_count)) {
                          $cer_count = $certificate_count;
                          if (in_array('expired', $cer_count)) {
                            if ($field_expiry_date < $current_date && !empty($field_expiry_date)) {
                              $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                              $row[$row_key]['name'] = $username;
                              $row[$row_key]['last_name'] = get_last_name($user->uid);
                              $row[$row_key]['asset'] = $value->title;
                              $row[$row_key]['job_title'] = $job->title;
                              $row[$row_key]['uid'] = $user->uid;
                              $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                              $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                              $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                              $row[$row_key]['course_status'] = $status;
                              $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                              $row[$row_key]['cert'] = $certi;
                              $row[$row_key]['month'] = $months;
                              $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                              $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                              $row[$row_key]['current_date'] = $current_date;
                              $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                              $output[$user->uid . $job_key] = $row;
                            }
                          }
                          if (in_array('three_to_one', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 3 && $months > 1);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                          if (in_array('six_to_three', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 6 && $months > 3);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                          if (in_array('one_month', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 1);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                        }
                        else {
                          if (!empty($field_expiry_date)) {
                            $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                            $row[$row_key]['name'] = $username;
                            $row[$row_key]['last_name'] = get_last_name($user->uid);
                            $row[$row_key]['asset'] = $value->title;
                            $row[$row_key]['job_title'] = $job->title;
                            $row[$row_key]['uid'] = $user->uid;
                            $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                            $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                            $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                            $row[$row_key]['course_status'] = $status;
                            $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                            $row[$row_key]['cert'] = $certi;
                            $row[$row_key]['month'] = $months;
                            $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                            $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                            $row[$row_key]['current_date'] = $current_date;
                            $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                            $output[$user->uid . $job_key] = $row;
                          }
                        }
                      }
                      else {
                      }
                    }
                    elseif (!empty($cer_start_date1) && !empty($cer_end_date1)) {
                      if ($field_start_date > $cer_start_date && $field_start_date < $cer_end_date) {
                        if (!empty($certificate_count)) {
                          $cer_count = $certificate_count;
                          if (in_array('expired', $cer_count)) {
                            if ($field_expiry_date < $current_date && !empty($field_expiry_date)) {
                              $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                              $row[$row_key]['name'] = $username;
                              $row[$row_key]['last_name'] = get_last_name($user->uid);
                              $row[$row_key]['asset'] = $value->title;
                              $row[$row_key]['job_title'] = $job->title;
                              $row[$row_key]['uid'] = $user->uid;
                              $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                              $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                              $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                              $row[$row_key]['course_status'] = $status;
                              $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                              $row[$row_key]['cert'] = $certi;
                              $row[$row_key]['month'] = $months;
                              $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                              $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                              $row[$row_key]['current_date'] = $current_date;
                              $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                              $output[$user->uid . $job_key] = $row;
                            }
                          }
                          if (in_array('six_to_three', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 6 && $months > 3);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                          if (in_array('three_to_one', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 3 && $months > 1);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                          if (in_array('one_month', $cer_count)) {
                            if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                              $cond = ($months <= 1);
                              if ($cond) {
                                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                                $row[$row_key]['name'] = $username;
                                $row[$row_key]['last_name'] = get_last_name($user->uid);
                                $row[$row_key]['asset'] = $value->title;
                                $row[$row_key]['job_title'] = $job->title;
                                $row[$row_key]['uid'] = $user->uid;
                                $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                                $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                                $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                                $row[$row_key]['course_status'] = $status;
                                $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                                $row[$row_key]['cert'] = $certi;
                                $row[$row_key]['month'] = $months;
                                $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                                $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                                $row[$row_key]['current_date'] = $current_date;
                                $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                                $output[$user->uid . $job_key] = $row;
                              }
                            }
                          }
                        }
                        else {
                          if (!empty($field_expiry_date)) {
                            $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                            $row[$row_key]['name'] = $username;
                            $row[$row_key]['last_name'] = get_last_name($user->uid);
                            $row[$row_key]['asset'] = $value->title;
                            $row[$row_key]['job_title'] = $job->title;
                            $row[$row_key]['uid'] = $user->uid;
                            $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                            $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                            $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                            $row[$row_key]['course_status'] = $status;
                            $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                            $row[$row_key]['cert'] = $certi;
                            $row[$row_key]['month'] = $months;
                            $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                            $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                            $row[$row_key]['current_date'] = $current_date;
                            $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                            $output[$user->uid . $job_key] = $row;
                          }
                        }
                      }
                      else {
                      }
                    }
                    else {
                      if (!empty($certificate_count)) {
                        $cer_count = $certificate_count;
                        if (in_array('expired', $cer_count)) {
                          if ($field_expiry_date < $current_date && !empty($field_expiry_date)) {
                            $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                            $row[$row_key]['name'] = $username;
                            $row[$row_key]['last_name'] = get_last_name($user->uid);
                            $row[$row_key]['asset'] = $value->title;
                            $row[$row_key]['job_title'] = $job->title;
                            $row[$row_key]['uid'] = $user->uid;
                            $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                            $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                            $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                            $row[$row_key]['course_status'] = $status;
                            $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                            $row[$row_key]['cert'] = $certi;
                            $row[$row_key]['month'] = $months;
                            $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                            $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                            $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                            $row[$row_key]['current_date'] = $current_date;
                            $output[$user->uid . $job_key] = $row;
                          }
                        }
                        if (in_array('six_to_three', $cer_count)) {
                          if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                            $cond = ($months <= 6 && $months > 3);
                            if ($cond) {
                              $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                              $row[$row_key]['name'] = $username;
                              $row[$row_key]['last_name'] = get_last_name($user->uid);
                              $row[$row_key]['asset'] = $value->title;
                              $row[$row_key]['job_title'] = $job->title;
                              $row[$row_key]['uid'] = $user->uid;
                              $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                              $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                              $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                              $row[$row_key]['course_status'] = $status;
                              $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                              $row[$row_key]['cert'] = $certi;
                              $row[$row_key]['month'] = $months;
                              $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                              $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                              $row[$row_key]['current_date'] = $current_date;
                              $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                              $output[$user->uid . $job_key] = $row;
                            }
                          }
                        }
                        if (in_array('three_to_one', $cer_count)) {
                          if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                            $cond = ($months <= 3 && $months > 1);
                            if ($cond) {
                              $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                              $row[$row_key]['name'] = $username;
                              $row[$row_key]['last_name'] = get_last_name($user->uid);
                              $row[$row_key]['asset'] = $value->title;
                              $row[$row_key]['job_title'] = $job->title;
                              $row[$row_key]['uid'] = $user->uid;
                              $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                              $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                              $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                              $row[$row_key]['course_status'] = $status;
                              $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                              $row[$row_key]['cert'] = $certi;
                              $row[$row_key]['month'] = $months;
                              $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                              $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                              $row[$row_key]['current_date'] = $current_date;
                              $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                              $output[$user->uid . $job_key] = $row;
                            }
                          }
                        }
                        if (in_array('one_month', $cer_count)) {
                          if ($field_expiry_date > $current_date && !empty($field_expiry_date)) {
                            $cond = ($months <= 1);
                            if ($cond) {
                              $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                              $row[$row_key]['name'] = $username;
                              $row[$row_key]['last_name'] = get_last_name($user->uid);
                              $row[$row_key]['asset'] = $value->title;
                              $row[$row_key]['job_title'] = $job->title;
                              $row[$row_key]['uid'] = $user->uid;
                              $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                              $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                              $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                              $row[$row_key]['course_status'] = $status;
                              $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                              $row[$row_key]['cert'] = $certi;
                              $row[$row_key]['month'] = $months;
                              $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                              $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                              $row[$row_key]['current_date'] = $current_date;
                              $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                              $output[$user->uid . $job_key] = $row;
                            }
                          }
                        }
                      }
                      else {
                        if (!empty($field_expiry_date)) {
                          $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                          $row[$row_key]['name'] = $username;
                          $row[$row_key]['uid'] = $user->uid;
                          $row[$row_key]['last_name'] = get_last_name($user->uid);
                          $row[$row_key]['asset'] = $value->title;
                          $row[$row_key]['job_title'] = $job->title;
                          $row[$row_key]['date'] = $user_certificate[$certi] . ' (' . $count_days . ' Days)';
                          $row[$row_key]['course_name'] = $user_certificate[$certi . '0'];
                          $row[$row_key]['accreditation'] = get_accreditation($user->uid, $user_certificate[$certi . '0'], $user_certificate[$certi . '4']);
                          $row[$row_key]['course_status'] = $status;
                          $row[$row_key]['provider_name'] = $user_certificate[$certi . '1'];
                          $row[$row_key]['cert'] = $certi;
                          $row[$row_key]['month'] = $months;
                          $row[$row_key]['field_start_date'] = $user_certificate[$certi . '2'];
                          $row[$row_key]['certificate_type_id'] = $user_certificate[$certi . '5'];
                          $row[$row_key]['current_date'] = $current_date;
                          $row[$row_key]['pid'] = get_course_paragraph_id($user->uid, $user_certificate[$certi . '5']);
                          $output[$user->uid . $job_key] = $row;
                        }
                      }
                    }
                  }
                  $row_key++;
                }
              }
            }
            $job_key++;
          }
        }
      }
      $response = ['data' => $output];
    }
    return new ResourceResponse($response);
  }

}
