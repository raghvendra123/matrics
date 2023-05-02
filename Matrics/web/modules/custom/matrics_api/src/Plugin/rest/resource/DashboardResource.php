<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a Dashboard Resource.
 *
 * @RestResource(
 *   id = "dashboard",
 *   label = @Translation("Dashboard"),
 *   uri_paths = {
 *     "canonical" = "/dashboard",
 *     "create" = "/dashboard"
 *   }
 * )
 */
class DashboardResource extends ResourceBase {

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
    $uid = \Drupal::currentUser()->id();
    $current_user = \Drupal::currentUser();
    $user_roles = $current_user->getRoles();

    $db = \Drupal::database();
    $dashboard = $db->select('dashboard_manage', 'u');
    $dashboard->fields('u', []);
    $dashboard_results = $dashboard->execute()->fetchAll();

    foreach ($dashboard_results as $key => $dashboard_result) {
      $dashboard_arr[$key]['id'] = $dashboard_result->id;
      $dashboard_arr[$key]['uid'] = $dashboard_result->uid;
      $dashboard_arr[$key]['tiles'] = $dashboard_result->tiles;
      $dashboard_arr[$key]['settings'] = $dashboard_result->settings;
      $dashboard_arr[$key]['tile_order'] = $dashboard_result->tile_order;
      $dashboard_arr[$key]['status'] = $dashboard_result->status;
      $dashboard_arr[$key]['chart_status'] = $dashboard_result->chart_status;
    }
    $all_chart = ['Booking', 'Booking Cancel',
      'Total Spend', 'Training Days',
      'Vendors', 'Courses',
      'Employees', 'Certificate',
      'Training Gap Score', 'Training Gap Count',
    ];
    $categories = ['June', 'July', 'Aug', 'September'];

    $position['course_booked'] = get_booking_count('course booked');
    $position['complete_count'] = get_booking_count('completed');
    $position['cacelled_count_customer'] = get_booking_count('cancelled', 'Cancelled by Customer');
    $position['cacelled_count_provider'] = get_booking_count('cancelled', 'Cancelled by provider');
    $position['cmt_fee_courses'] = get_course_fee_sum('node_revision__field_cm', 'field_cm_value');
    $position['customer_fee_courses'] = get_course_fee_sum('node_revision__field_customer_fee', 'field_customer_fee_value');

    // Employee count.
    $query = $db->select('users_field_data', 'u');
    $query->fields('u', ["uid"]);
    $query->join('user__roles', 'r', 'r.entity_id = u.uid');
    $query->condition('r.roles_target_id', 'individual_employee', "=");
    $position['employees_registered'] = $query->countQuery()->execute()->fetchField();

    // Vendor Count.
    $vendor = $db->select('node_field_data', 'n');
    $vendor->fields('n', ['nid', 'title']);
    $vendor->condition('n.type', 'vendor', "=");
    $position['approved_training_provider'] = $vendor->countQuery()->execute()->fetchField();

    // Training provider used.
    $view = Views::getView('courses_list');
    $view->setDisplay('block_3');
    $view->execute();
    $position['training_provider_used'] = $view->result[0]->users_field_data_name;

    // Number of training days.
    $position['course_duration'] = get_course_result_days();

    // Certificate count.
    $position['certificate_issued'] = get_all_certificate_count();

    // Training gap countP3terP4n!!.
    $position['training_gap_count'] = training_gap_count();

    // Training gap score.
    $position['training_gap_score'] = training_gap_score();

    // To be actioned.
    $position['to_be_actioned'] = to_be_actioned();

    // Course expired.
    $position['course_expired'] = course_expired();

    // Cancellation fees.
    $position['cancellation_fees_courses'] = cancellation_fees_courses();
    // Certificate pending.
    $position['certificate_pending'] = training_count_certifcate_pending();
    // Course count.
    $position['course_passed'] = get_course_result('completed');
    $position['course_failed'] = get_course_result('failed');
    // $position['course_resit'] = get_course_result('resit');
    $roles = \Drupal::currentUser()->getRoles();
    $check['check'] = 1;
    if (in_array('tms_admins_', $roles)) {
      $check['check'] = 0;
    }
    $title_config = \Drupal::configFactory()->getEditable('matrics_dashboard.title_settings');
    if ($title_config) {
      $user_dashboard_title = !empty($title_config->get('my_dashboard_' . $uid)) ? $title_config->get('my_dashboard_' . $uid) : t('Your personalised dashboard');
    }
    else {
      $user_dashboard_title = t('Your personalised dashboard');
    }
    $response = [
      'counts' => $position,
      'tile_status' => $dashboard_arr,
      'chart_name' => $all_chart,
      'categories' => $categories,
      '#dashboard_title' => $user_dashboard_title,
      '#uroles' => $check,
    ];
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
    $response = ['message' => $this->t('Please Post any key of asset, job title, country, start date and end date.')];
    if (!empty($data)) {
      $db = \Drupal::database();
      $dashboard = $db->select('dashboard_manage', 'u');
      $dashboard->fields('u', []);
      $dashboard_results = $dashboard->execute()->fetchAll();
      foreach ($dashboard_results as $key => $dashboard_result) {
        $dashboard_arr[$key]['id'] = $dashboard_result->id;
        $dashboard_arr[$key]['uid'] = $dashboard_result->uid;
        $dashboard_arr[$key]['tiles'] = $dashboard_result->tiles;
        $dashboard_arr[$key]['settings'] = $dashboard_result->settings;
        $dashboard_arr[$key]['tile_order'] = $dashboard_result->tile_order;
        $dashboard_arr[$key]['status'] = $dashboard_result->status;
        $dashboard_arr[$key]['chart_status'] = $dashboard_result->chart_status;
      }
      $all_chart = [
        'Booking', 'Booking Cancel',
        'Total Spend', 'Training Days',
        'Vendors', 'Courses',
        'Employees', 'Certificate',
        'Training Gap Score', 'Training Gap Count',
      ];
      $categories = ['June', 'July', 'Aug', 'September'];
      $asset_id = NULL;
      if (!empty($data['asset_id'])) {
        $asset_id = $data['asset_id'];
      }
      $job_title_id = NULL;
      if (!empty($data['job_title_id'])) {
        $job_title_id = $data['job_title_id'];
      }
      $region = NULL;
      if (!empty($data['region'])) {
        $asset_id = $data['region'];
      }
      $start_date = NULL;
      if (!empty($data['start_date'])) {
        $start_date = $data['start_date'];
      }
      $end_date = NULL;
      if (!empty($data['end_date'])) {
        $end_date = $data['end_date'];
      }
      $position['course_booked'] = get_booking_count('course booked', NULL, $asset_id, $job_title_id, $region, $start_date, $end_date);
      $position['complete_count'] = get_booking_count('completed', NULL, $asset_id, $job_title_id, $region, $start_date, $end_date);
      $position['cacelled_count_customer'] = get_booking_count('cancelled', 'Cancelled by Customer', $asset_id, $job_title_id, $region, $start_date, $end_date);
      $position['cacelled_count_provider'] = get_booking_count('cancelled', 'Cancelled by provider﻿', $asset_id, $job_title_id, $region, $start_date, $end_date);
      $position['cmt_fee_courses'] = get_course_fee_sum('node__field_cm', 'field_cm_value');
      $position['customer_fee_courses'] = get_course_fee_sum('node__field_customer_fee', 'field_customer_fee_value');
      $position['employees_registered'] = get_employee_count($asset_id, $job_title_id, $region);
      // Vendor Count.
      $vendor = $db->select('node_field_data', 'n');
      $vendor->fields('n', ['nid', 'title']);
      $vendor->condition('n.type', 'vendor', "=");
      $position['approved_training_provider'] = $vendor->countQuery()->execute()->fetchField();
      // Training provider used.
      $view = Views::getView('courses_list');
      $view->setDisplay('block_3');
      $view->execute();
      $position['training_provider_used'] = $view->result[0]->users_field_data_name;

      // Number of training days.
      $position['course_duration'] = get_course_result_days($asset_id, $job_title_id, $region, $start_date, $end_date);

      // Certificate count.
      $position['certificate_issued'] = get_all_certificate_count($asset_id, $job_title_id, $region, $start_date, $end_date);

      // Training gap countP3terP4n!!.
      $position['training_gap_count'] = training_gap_count($asset_id, $job_title_id, $region, $start_date, $end_date);

      // Training gap score.
      $position['training_gap_score'] = training_gap_score($asset_id, $job_title_id, $region, $start_date, $end_date);

      // To be actioned.
      $position['to_be_actioned'] = to_be_actioned($asset_id, $job_title_id, $region);

      // Course expired.
      $position['course_expired'] = course_expired($asset_id, $job_title_id, $region, $start_date, $end_date);

      // Cancellation fees.
      $position['cancellation_fees_courses'] = cancellation_fees_courses($asset_id, $job_title_id, $region, $start_date, $end_date);
      // Certificate pending.
      $position['certificate_pending'] = training_count_certifcate_pending($asset_id, $job_title_id, $region);
      // Course count.
      $position['course_passed'] = get_course_result('completed', $asset_id, $job_title_id, $region, $start_date, $end_date);
      $position['course_failed'] = get_course_result('failed', $asset_id, $job_title_id, $region, $start_date, $end_date);
      // $position['course_resit'] = get_course_result('resit', $asset_id, $job_title_id, $region, $start_date, $end_date);
      $response = [
        'counts' => $position,
        'tile_status' => $dashboard_arr,
        'chart_name' => $all_chart,
        'categories' => $categories,
      ];
    }
    return new ResourceResponse($response);
  }

}
