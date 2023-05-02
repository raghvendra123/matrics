<?php

namespace Drupal\matrics_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\charts\Services\ChartsSettingsServiceInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides route responses for landing-page routing.
 */
class DashboardTiles extends ControllerBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\Database\Connection
   *  Database connection service.
   */
  protected $connection;

  /**
   * The charts settings.
   *
   * @var \Drupal\charts\Services\ChartsSettingsServiceInterface
   */
  protected $chartSettings;

  /**
   * Constructs a new SiteConfiguration Form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config data.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\charts\Services\ChartsSettingsServiceInterface $chartSettings
   *   The chartsettings.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, Connection $connection, ChartsSettingsServiceInterface $chartSettings) {
    $this->configfactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $connection;
    $this->chartSettings = $chartSettings->getChartsSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('charts.settings')
    );
  }
  
  /**
   * Returns a page title.
   */
  public function getTitle($id) {
    $title = '';
    switch ($id) {
      case 'employee':
        $title = $this->t('Employee');
        break;
        
      case 'course_passed':
        $title = $this->t('Course passed');
        break;
        
      case 'course_failed':
        $title = $this->t('Course failed');
        break;
          
      case 'booking_in_progress':
        $title = $this->t('Booking in progress');
        break;
        
      case 'course_booked':
        $title = $this->t('Booked course');
        break;
        
      case 'booking_completed':
        $title = $this->t('Booking completed');
        break;
        
      case 'booking_cancelled_by_customer':
        $title = $this->t('Booking cancelled by customer');
        break;
        
      case 'booking_cancelled_by_provider':
        $title = $this->t('Booking cancelled by provider');
        break;
        
      case 'total_spend_Customer':
        $title = $this->t('Total spend customer');
        break;
        
      case 'total_spend_TMS':
        $title = $this->t('Total spend TMS');
        break;
        
      case 'course_duration':
        $title = $this->t('Course Duration');
        break;
      case 'course_expired':
        $title = $this->t('Course Expired');
        break;
      case 'cancellation_fees_courses':
        $title = $this->t('Cancellation fees courses');
        break;  
    }
    return  $title;
  }
  
  /**
   * {@inheritdoc}
   */
  public function employee($id) {
    $customerid = get_session_customerid();
    switch ($id) {
      case 'employee':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', 'individual_employee', "=");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $result = $query->execute()->fetchAll();

        foreach ($result as $value) {
          $rows[] = [$value->uid, $value->name];
        }
        $header = ['User Id', 'Name'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'course_passed':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_certificates', 'ce', 'ce.entity_id = u.uid');
        $query->join('paragraph__field_enable', 'e', 'e.entity_id = ce.field_certificates_target_id');
        $query->condition('e.field_enable_value', 1);
        $query->join('paragraph__field_certificate_type', 't', 't.entity_id = ce.field_certificates_target_id');
        $query->fields('t', ['field_certificate_type_target_id']);
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->join('paragraph__field_status ', 's', 's.entity_id = d.id');
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->join('node_field_data', 'fd', 'cou.field_course__target_id = fd.nid');
        $query->join('paragraph__field_certificate_type', 'ct', 'ct.entity_id = c.field_course_details_target_id');
        $query->fields('ct', ['field_certificate_type_target_id']);
        $query->fields('d', ["id"]);
        $query->fields('cou', ["field_course__target_id"]);
        $query->fields('fd', ["title"]);
        $query->fields('s', ["field_status_value"]);
        $query->condition('s.field_status_value', 'completed', "=");
        $rows = [];
        $result = $query->execute()->fetchAll();
        if (!empty($result)) {
          foreach ($result as $value) {
            if ($value->field_certificate_type_target_id == $value->ct_field_certificate_type_target_id) {
              $rows[] = [$value->name, $value->title, 'Passed'];
            }
          }
        }
        $header = ['Name', 'Course Name', 'Status'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'course_failed':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_certificates', 'ce', 'ce.entity_id = u.uid');
        $query->join('paragraph__field_enable', 'e', 'e.entity_id = ce.field_certificates_target_id');
        $query->condition('e.field_enable_value', 1);
        $query->join('paragraph__field_certificate_type', 't', 't.entity_id = ce.field_certificates_target_id');
        $query->fields('t', ['field_certificate_type_target_id']);
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->join('paragraph__field_status ', 's', 's.entity_id = d.id');
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->join('node_field_data', 'fd', 'cou.field_course__target_id = fd.nid');
        $query->join('paragraph__field_certificate_type', 'ct', 'ct.entity_id = c.field_course_details_target_id');
        $query->fields('ct', ['field_certificate_type_target_id']);
        $query->fields('d', ["id"]);
        $query->fields('cou', ["field_course__target_id"]);
        $query->fields('fd', ["title"]);
        $query->fields('s', ["field_status_value"]);
        $query->condition('s.field_status_value', 'failed', "=");
        $rows = [];
        $result = $query->execute()->fetchAll();
        if (!empty($result)) {
          foreach ($result as $value) {
            if ($value->field_certificate_type_target_id == $value->ct_field_certificate_type_target_id) {
              $rows[] = [
                $value->name,
                $value->title,
                $value->field_status_value,
              ];
            }
          }
        }
        $header = ['Name', 'Course Name', 'Status'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'booking_in_progress':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee', 'mnager'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->join('paragraph__field_status', 's', 's.entity_id = d.id');
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->join('node_field_data', 'fd', 'cou.field_course__target_id = fd.nid');
        $query->fields('d', ["id"]);
        $query->fields('cou', ["field_course__target_id"]);
        $query->fields('fd', ["title"]);
        $query->fields('s', ["field_status_value"]);
        $query->condition('s.field_status_value', 'in-progress', "=");
        $result = $query->execute()->fetchAll();

        foreach ($result as $value) {
          $rows[] = [$value->name, $value->title, $value->field_status_value];
        }
        $header = ['Name', 'Course Name', 'Status'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'course_booked':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_certificates', 'ce', 'ce.entity_id = u.uid');
        $query->join('paragraph__field_enable', 'e', 'e.entity_id = ce.field_certificates_target_id');
        $query->condition('e.field_enable_value', 1);
        $query->join('paragraph__field_certificate_type', 't', 't.entity_id = ce.field_certificates_target_id');
        $query->fields('t', ['field_certificate_type_target_id']);
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->join('paragraph__field_status', 's', 's.entity_id = d.id');
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->join('paragraph__field_certificate_type', 'ct', 'ct.entity_id = c.field_course_details_target_id');
        $query->fields('ct', ['field_certificate_type_target_id']);
        $query->join('paragraph__field_enable', 'cen', 'cen.entity_id = c.field_course_details_target_id');
        $query->condition('cen.field_enable_value', 1);
        $query->join('node_field_data', 'fd', 'cou.field_course__target_id = fd.nid');
        $query->fields('d', ["id"]);
        $query->fields('cou', ["field_course__target_id"]);
        $query->fields('fd', ["title"]);
        $query->fields('s', ["field_status_value"]);
        $query->condition('s.field_status_value', 'course booked', "=");

        $result = $query->execute()->fetchAll();
        foreach ($result as $value) {
          if ($value->field_certificate_type_target_id == $value->ct_field_certificate_type_target_id) {
            $rows[] = [$value->name, $value->title, $value->field_status_value];
          }
        }
        $header = ['Name', 'Course Name', 'Status'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'booking_completed':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->join('paragraph__field_status', 's', 's.entity_id = d.id');
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->join('node_field_data', 'fd', 'cou.field_course__target_id = fd.nid');
        $query->join('paragraph__field_close_booking', 'cb', 'cb.entity_id = c.field_course_details_target_id');
        $query->condition('cb.field_close_booking_value', 1);
        $query->fields('d', ["id"]);
        $query->fields('cou', ["field_course__target_id"]);
        $query->fields('fd', ["title"]);
        $query->fields('s', ["field_status_value"]);

        $query->condition('s.field_status_value', 'completed', "=");

        $result = $query->execute()->fetchAll();

        foreach ($result as $value) {
          $rows[] = [$value->name, $value->title, 'Passed'];
        }
        $header = ['Name', 'Course Name', 'Status'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'booking_cancelled_by_customer':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_certificates', 'ce', 'ce.entity_id = u.uid');
        $query->join('paragraph__field_enable', 'e', 'e.entity_id = ce.field_certificates_target_id');
        $query->condition('e.field_enable_value', 1);
        $query->join('paragraph__field_certificate_type', 't', 't.entity_id = ce.field_certificates_target_id');
        $query->fields('t', ['field_certificate_type_target_id']);
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->join('paragraph__field_status', 's', 's.entity_id = d.id');
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->join('paragraph__field_enable', 'cen', 'cen.entity_id = c.field_course_details_target_id');
        $query->condition('cen.field_enable_value', 1);
        $query->join('node_field_data', 'fd', 'cou.field_course__target_id = fd.nid');
        $query->fields('d', ["id"]);
        $query->fields('cou', ["field_course__target_id"]);
        $query->fields('fd', ["title"]);
        $query->fields('s', ["field_status_value"]);

        $query->join('paragraph__field_cancelled_by', 'cb', 'cb.entity_id = d.id');
        $query->fields('cb', ["field_cancelled_by_value"]);
        $query->condition('cb.field_cancelled_by_value', 'Cancelled by Customer');

        $query->condition('s.field_status_value', 'cancelled', "=");

        $result = $query->execute()->fetchAll();

        foreach ($result as $value) {
          $rows[] = [$value->name, $value->title, $value->field_status_value];
        }
        $header = ['Name', 'Course Name', 'Status'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'booking_cancelled_by_provider':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_certificates', 'ce', 'ce.entity_id = u.uid');
        $query->join('paragraph__field_enable', 'e', 'e.entity_id = ce.field_certificates_target_id');
        $query->condition('e.field_enable_value', 1);
        $query->join('paragraph__field_certificate_type', 't', 't.entity_id = ce.field_certificates_target_id');
        $query->fields('t', ['field_certificate_type_target_id']);
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->join('paragraph__field_status', 's', 's.entity_id = d.id');
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->join('paragraph__field_enable', 'cen', 'cen.entity_id = c.field_course_details_target_id');
        $query->condition('cen.field_enable_value', 1);
        $query->join('node_field_data', 'fd', 'cou.field_course__target_id = fd.nid');
        $query->fields('d', ["id"]);
        $query->fields('cou', ["field_course__target_id"]);
        $query->fields('fd', ["title"]);
        $query->fields('s', ["field_status_value"]);

        $query->join('paragraph__field_cancelled_by', 'cb', 'cb.entity_id = d.id');
        $query->fields('cb', ["field_cancelled_by_value"]);
        $query->condition('cb.field_cancelled_by_value', 'Cancelled by provider');

        $query->condition('s.field_status_value', 'cancelled', "=");

        $result = $query->execute()->fetchAll();
        $rows = [];
        foreach ($result as $value) {
          $rows[] = [$value->name, $value->title, $value->field_status_value];
        }
        $header = ['Name', 'Course Name', 'Status'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'total_spend_Customer':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->condition('u.uid', '1', '!=');
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_course_details', 'crs', 'crs.entity_id = u.uid');
        $query->join('paragraph_revision__field_course_', 'p_crs', 'p_crs.entity_id = crs.field_course_details_target_id');
        $query->join('node_field_data', 'n_data', 'n_data.nid = p_crs.field_course__target_id');
        $query->fields('n_data', ['nid', 'title']);
        $query->join('node_revision__field_customer_fee', 'n_cm', 'n_cm.entity_id = n_data.nid');
        $query->fields('n_cm', ['field_customer_fee_value']);
        $result = $query->distinct()->execute()->fetchAll();
        $sum = 0;

        foreach ($result as $value) {
          $rows[] = [$value->title, $value->field_customer_fee_value];
        }
        $header = ['Course Name', 'Customer Charge'];
        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'total_spend_TMS':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->condition('u.uid', '1', '!=');
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_course_details', 'crs', 'crs.entity_id = u.uid');
        $query->join('paragraph_revision__field_course_', 'p_crs', 'p_crs.entity_id = crs.field_course_details_target_id');
        $query->join('node_field_data', 'n_data', 'n_data.nid = p_crs.field_course__target_id');
        $query->fields('n_data', ['nid', 'title']);
        $query->join('node_revision__field_cm', 'n_cm', 'n_cm.entity_id = n_data.nid');
        $query->fields('n_cm', ['field_cm_value']);
        $result = $query->distinct()->execute()->fetchAll();
        $sum = 0;

        foreach ($result as $value) {
          $rows[] = [$value->title, $value->field_cm_value];
        }
        $header = ['Course Name', 'TMS Charge'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'course_duration':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->join('paragraph__field_course_result ', 's', 's.entity_id = d.id');
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->join('node_field_data', 'fd', 'cou.field_course__target_id = fd.nid');
        $query->join('node__field_course_duration', 'bo', 'cou.field_course__target_id = bo.entity_id');
        $query->fields('d', ["id"]);
        $query->fields('cou', ["field_course__target_id"]);
        $query->fields('fd', ["title"]);
        $query->fields('bo', ["field_course_duration_value"]);
        $query->fields('s', ["field_course_result_value"]);
        $query->condition('s.field_course_result_value', 'passed', "=");

        $result = $query->execute()->fetchAll();

        foreach ($result as $value) {
          $rows[] = [$value->title, $value->field_course_duration_value];
        }
        $header = ['Course Name', 'Number of Training Days'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'course_expired':
        $db = \Drupal::database();
        $query = $db->select('users_field_data', 'u');
        $query->fields('u', ["uid", "name"]);
        $query->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->join('paragraphs_item_field_data', 'd', 'd.id = c.field_course_details_target_id');
        $query->fields('d', ["id"]);
        $query->join('paragraph__field_course_', 'cou', 'cou.entity_id = d.id');
        $query->fields('cou', ["field_course__target_id"]);
        $query->join('paragraph__field_expiry_date ', 'e', 'e.entity_id = d.id');
        $query->fields('e', ["field_expiry_date_value"]);
        $query->join('node_field_data', 'n', 'cou.field_course__target_id = n.nid');
        $query->fields('n', ["title"]);

        $result = $query->execute()->fetchAll();

        foreach ($result as $value) {
          $expiry = strtotime($value->field_expiry_date_value);
          $current_date = new DrupalDateTime();
          $current_date = strtotime($current_date);
          if ($expiry < $current_date) {
            $rows[] = [
              $value->name,
              $value->title,
              $value->field_expiry_date_value,
            ];
          }
        }
        $header = [
          'User Name',
          'Course Name',
          'Expiry Date',
        ];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;

      case 'cancellation_fees_courses':
        $query = \Drupal::database()->select('users_field_data', 'u')
          ->fields('u', ['name', 'uid']);
        if (!empty($customerid)) {
          $query->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query->fields('cu', ['field_customer_target_id']);
          $query->condition('cu.field_customer_target_id', $customerid);
        }
        $query->join('user__field_course_details', 'c', 'c.entity_id = u.uid');
        $query->fields('c', ['field_course_details_target_id']);
        $query->join('paragraph__field_course_', 'se', 'se.entity_id = c.field_course_details_target_id');
        $query->fields('se', ['field_course__target_id']);
        $query->join('node_field_data', 'n', 'se.field_course__target_id = n.nid');
        $query->fields('n', ["title"]);
        // $query2->condition('f.title', 'Course 2');
        $query->join('paragraph__field_status', 'ps', 'ps.entity_id = c.field_course_details_target_id');
        $query->fields('ps', ['field_status_value']);
        $query->condition('ps.field_status_value', 'cancelled');
        $query->join('paragraph__field_cancelled_fee', 'f', 'f.entity_id = c.field_course_details_target_id');
        $query->fields('f', ['field_cancelled_fee_value']);

        $result = $query->execute()->fetchAll();

        foreach ($result as $value) {
          // $expiry = strtotime($value->field_expiry_date_value);
          // $current_date = new \Drupal\Core\Datetime\DrupalDateTime();
          // $current_date = strtotime($current_date);
          // if($expiry < $current_date) {
          // }
          $rows[] = [
            $value->name,
            $value->title,
            $value->field_status_value,
            $value->field_cancelled_fee_value,
          ];
        }
        $header = ['Name', 'Course Name', 'Course Status', 'Cancellation Fee'];

        $render[] = [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
        ];
        return $render;
    }
    if ($id == 'employee') {
    }
  }

}
