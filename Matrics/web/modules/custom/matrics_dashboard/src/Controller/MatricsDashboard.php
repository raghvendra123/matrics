<?php

namespace Drupal\matrics_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\charts\Services\ChartsSettingsServiceInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;
use Drupal\views\Views;
use Drupal\user\Form\UserLoginForm;

/**
 * Provides route responses for landing-page routing.
 */
class MatricsDashboard extends ControllerBase {

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
   * {@inheritdoc}
   */
  public function task_complete($id) {
    $node = Node::load($id);
    $node->set("field_task_status", 'completed');
    $node->save();
    \Drupal::messenger()->addMessage($node->getTitle() . ' is mark as completed.');
    $response = new RedirectResponse('/task');
    return $response->send();
  }

  /**
   * {@inheritdoc}
   */
  public function dashboard() {
    $uid = \Drupal::currentUser()->id();
    $current_user = \Drupal::currentUser();
    $user_roles = $current_user->getRoles();
    if (in_array('individual_employee', $user_roles)) {
      $url = "/user";
      $response = new RedirectResponse($url);
      return $response->send();
    }

    $form = \Drupal::formBuilder()->getForm(UserLoginForm::class);
    $render = \Drupal::service('renderer');
    $login_form = $render->renderPlain($form);
    if (\Drupal::currentUser()->isAnonymous()) {
      $config = \Drupal::service('config.factory')->getEditable('matrics_dashboard.settings');
      $file = File::load($config->get('image')[0]);
      // $uri = \Drupal::service('file_system')->realpath($file->getFileUri());
      $file_url_generator = \Drupal::service('file_url_generator');
      $image = $file_url_generator->generateAbsoluteString($file->getFileUri());
      $config->set('img', $image)->save();
      // dump(file_create_url($file->getFileUri())); die;.
      return [
        '#theme' => 'dashboard_login',
        '#items' => $config->get(),
        '#blocks' => $login_form,
        '#attached' => [
          'library' => [
            'matrics_dashboard/matrics_styles',
          ],
        ],
        '#cache' => ['max-age' => 0],
      ];
    }
    else {
      $db = \Drupal::database();
      $dashboard = $db->select('dashboard_manage', 'u');
      $dashboard->fields('u', []);
      $dashboard_result = $dashboard->execute()->fetchAll();
      // $position['inprogress_count'] = get_booking_count('in-progress');.
      $position['course_booked'] = get_booking_count('course booked');
      $position['complete_count'] = get_booking_count('completed');
      $position['cacelled_count_customer'] = get_booking_count('cancelled', 'Cancelled by Customer');
      $position['cacelled_count_provider'] = get_booking_count('cancelled', 'Cancelled by provider');
      $position['cmt_fee_courses'] = get_course_fee_sum('node_revision__field_cm', 'field_cm_value');
      $position['customer_fee_courses'] = get_course_fee_sum('node_revision__field_customer_fee', 'field_customer_fee_value');

      $position['employees_registered'] = get_employee_count();
      // Vendor Count.
      $customerid = get_session_customerid();
      $vendor = $db->select('node_field_data', 'n');
      $vendor->fields('n', ['nid', 'title']);
      $vendor->condition('n.type', 'vendor', "=");
      if (!empty($customerid)) {
        $vendor->join('node__field_customer', 'cu', 'n.nid = cu.entity_id');
        $vendor->fields('cu', ['field_customer_target_id']);
        $vendor->condition('cu.field_customer_target_id', $customerid);
      }
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

      $library = $this->chartSettings['library'];
      if (empty($library)) {
        $this->messenger->addError($this->t('You need to first configure Charts default settings'));
        return [];
      }

      $all_chart = [
        'Booking',
        'Booking Cancel',
        'Total Spend',
        'Training Days',
        'Training Providers',
        'Courses',
        'Employees',
        'Certificate',
        'Training Gap Score',
        'Training Gap Count',
      ];
      $chart_output = [];
      foreach ($all_chart as $all) {
        $pie_chart = '';
        $seriesData = [];
        $seriesData[] = [
          'name' => '',
          'color' => '',
          'type' => 'column',
          'data' => [0],
        ];
        // $seriesData = [];
        $options = [];
        if ($all == 'Booking' || $all == 'Booking Cancel' || $all == 'Total Spend' || $all == 'Training Providers' || $all == 'Courses' || $all == 'Certificate') {
          $type = 'pie';
        }
        if ($all == 'Training Days' || $all == 'Employees' || $all == 'Training Gap Score' || $all == 'Training Gap Count') {
          $type = 'line';
        }

        // Customize options here.
        $options = [
          'type' => $type,
          'title' => $all,
          'xaxis_title' => $this->t('X-Axis'),
          'yaxis_title' => $this->t('Y-Axis'),
          'yaxis_min' => '',
          'yaxis_max' => '',
          'three_dimensional' => FALSE,
          'title_position' => 'out',
          'legend_position' => 'right',
          'data_labels' => $this->chartSettings['data_labels'],
          'tooltips' => $this->chartSettings['tooltips'],
          // 'grouping'   => TRUE,
          'colors'   => $this->chartSettings['colors'],
          'min'   => $this->chartSettings['min'],
          'max'   => $this->chartSettings['max'],
          'yaxis_prefix'   => $this->chartSettings['yaxis_prefix'],
          'yaxis_suffix'   => $this->chartSettings['yaxis_suffix'],
          'data_markers'   => $this->chartSettings['data_markers'],
          'red_from'   => $this->chartSettings['red_from'],
          'red_to'   => $this->chartSettings['red_to'],
          'yellow_from'   => $this->chartSettings['yellow_from'],
          'yellow_to'   => $this->chartSettings['yellow_to'],
          'green_from'   => $this->chartSettings['green_from'],
          'green_to'   => $this->chartSettings['green_to'],
        ];

        // Sample data format.
        $categories = ['June', 'July', 'Aug', 'September'];

        switch ($all) {
          case 'Booking':
            if ($dashboard_result[0]->chart_status == 1) {
              $seriesData[] = [
                'name' => 'Booked Course',
                'color' => '#C6EFCE',
                'type' => $this->chartSettings['type'],
                'data' => [$position['course_booked']],
              ];
            }
            if ($dashboard_result[1]->chart_status == 1) {
              $seriesData[] = [
                'name' => 'Bookings completed',
                'color' => '#FFCE33',
                'type' => 'column',
                'data' => [$position['complete_count']],
              ];
            }
            break;

          case 'Booking Cancel':
            if ($dashboard_result[2]->chart_status == 1) {
              $seriesData[] = [
                'name' => 'Bookings cancelled by customer',
                'color' => '#FFEB9C',
                'type' => 'column',
                'data' => [$position['cacelled_count_customer']],
              ];
            }
            if ($dashboard_result[3]->chart_status == 1) {
              $seriesData[] = [
                'name' => 'Booking cancelled by provider',
                'color' => '#FFC7CE',
                'type' => 'column',
                'data' => [$position['cacelled_count_provider']],
              ];
            }
            break;

          case 'Total Spend':
            if ($dashboard_result[4]->chart_status == 1) {
              $seriesData[] = [
                'name' => 'Total spend – TMS charges',
                'color' => '#FFEB9C',
                'type' => 'column',
                'data' => [$position['cmt_fee_courses']],
              ];
            }
            if ($dashboard_result[5]->chart_status == 1) {
              $seriesData[] = [
                'name' => 'Total spend – training courses',
                'color' => '#FFC7CE',
                'type' => 'column',
                'data' => [$position['customer_fee_courses']],
              ];
            }
            if ($dashboard_result[6]->chart_status == 1) {
              $seriesData[] = [
                'name' => 'Total spend – cancellation fees',
                'color' => '#C6EFCE',
                'type' => 'column',
                'data' => [$position['cancellation_fees_courses']],
              ];
            }
            break;

          case 'Training Days':
            if ($dashboard_result[7]->chart_status == 1) {
              $months = [];
              $currentMonth = (int) date('m');
              for ($x = $currentMonth; $x < $currentMonth + 6; $x++) {
                $months[] = date('M', mktime(0, 0, 0, $x, 1));
              }
              $categories = $months;
              $seriesData[] = [
                'name' => 'Number of training days',
                'color' => '#000000',
                'type' => 'column',
                'data' => [$position['course_duration']],
              ];
            }
            break;

          case 'Training Providers':
            $seriesData[] = [
              'name' => 'Training providers used',
              'color' => '#FFEB9A',
              'type' => 'column',
              'data' => [$position['training_provider_used']],
            ];
            $seriesData[] = [
              'name' => 'Approved training providers',
              'color' => '#FFC7CB',
              'type' => 'column',
              'data' => [$position['approved_training_provider']],
            ];
            break;

          case 'Courses':
            $seriesData[] = [
              'name' => 'Courses passed',
              'color' => '#FFEB9A',
              'type' => 'column',
              'data' => [$position['course_passed']],
            ];
            $seriesData[] = [
              'name' => 'Courses failed',
              'color' => '#FFC7CB',
              'type' => 'column',
              'data' => [$position['course_failed']],
            ];
            // $seriesData[] = [
            //   'name' => 'Courses resit',
            //   'color' => '#FFC7CB',
            //   'type' => 'column',
            //   'data' => [$position['course_resit']],
            // ];
            $seriesData[] = [
              'name' => 'To Be Actioned',
              'color' => '#C6EFCE',
              'type' => 'column',
              'data' => [$position['to_be_actioned']],
            ];
            $seriesData[] = [
              'name' => 'Course Expired',
              'color' => '#FFCE33',
              'type' => 'column',
              'data' => [$position['course_expired']],
            ];
            break;

          case 'Employees':
            $months = [];
            $currentMonth = (int) (date('m', '-6 month'));

            for ($x = $currentMonth; $x < $currentMonth + 6; $x++) {
              $months[] = date('M', mktime(0, 0, 0, $x, 1));
            }
            $categories = $months;
            $seriesData[] = [
              'name' => 'Employees registered',
              'color' => '#000000',
              'type' => 'column',
              'data' => [(int) $position['employees_registered']],
            ];
            break;

          case 'Certificate':
            $seriesData[] = [
              'name' => 'Certificates issued',
              'color' => '#FFEB9A',
              'type' => 'column',
              'data' => [$position['certificate_issued']],
            ];
            $seriesData[] = [
              'name' => 'Certificates pending',
              'color' => '#FFC7CB',
              'type' => 'column',
              'data' => [$position['certificate_pending']],
            ];
            break;

          case 'Training Gap Score':
            if ($dashboard_result[16]->chart_status == 1) {
              $months = [];
              $currentMonth = (int) date('m');
              for ($x = $currentMonth; $x < $currentMonth + 6; $x++) {
                $months[] = date('M', mktime(0, 0, 0, $x, 1));
              }
              $categories = $months;
              $seriesData[] = [
                'name' => 'Training Gap Score',
                'color' => '#000000',
                'type' => 'column',
                'data' => [$position['training_gap_score']],
              ];
            }
            break;

          case 'Training Gap Count':
            if ($dashboard_result[17]->chart_status == 1) {
              $months = [];
              $currentMonth = (int) date('m');
              for ($x = $currentMonth; $x < $currentMonth + 6; $x++) {
                $months[] = date('M', mktime(0, 0, 0, $x, 1));
              }
              $categories = $months;
              $seriesData[] = [
                'name' => 'Training Gap Count',
                'color' => '#000000',
                'type' => 'column',
                'data' => [$position['training_gap_count']],
              ];
            }
            break;

          default:

        }
        $build = [];
        // Creates a UUID for the chart ID.
        $uuid_service = \Drupal::service('uuid');
        $uuid = $uuid_service->generate();
        $chartId = 'chart-' . $uuid;

        $build = [
          '#theme' => 'charts_api_example',
          '#library' => (string) $library,
          '#categories' => $categories,
          '#seriesData' => $seriesData,
          '#options' => $options,
          '#id' => $chartId,
          '#override' => [],
        ];
        $render = \Drupal::service('renderer');
        $pie_chart = $render->render($build);

        switch ($all) {
          case 'Booking':
            if (($dashboard_result[0]->chart_status == 1 || $dashboard_result[1]->chart_status == 1)) {
              $chart_output['booking'] = $pie_chart;
            }
            break;

          case 'Booking Cancel':
            if (($dashboard_result[2]->chart_status == 1 || $dashboard_result[3]->chart_status == 1)) {
              $chart_output['booking_cancel'] = $pie_chart;
            }
            break;

          case 'Total Spend':
            if (($dashboard_result[4]->chart_status == 1 || $dashboard_result[5]->chart_status == 1 || $dashboard_result[6]->chart_status == 1)) {
              $chart_output['total_spend'] = $pie_chart;
            }
            break;

          case 'Training Days':
            if ($dashboard_result[7]->chart_status == 1) {
              $chart_output['training_days'] = $pie_chart;
            }
            break;

          case 'Training Providers':
            if (($dashboard_result[8]->chart_status == 1 || $dashboard_result[9]->chart_status == 1)) {
              $chart_output['training_providers'] = $pie_chart;
            }
            break;

          case 'Courses':
            if (($dashboard_result[10]->chart_status == 1 || $dashboard_result[11]->chart_status == 1 || $dashboard_result[12]->chart_status == 1) || $dashboard_result[18]->chart_status == 1 || $dashboard_result[19]->chart_status == 1) {
              $chart_output['courses'] = $pie_chart;
            }
            break;

          case 'Employees':
            if ($dashboard_result[13]->chart_status == 1) {
              $chart_output['employees'] = $pie_chart;
            }
            break;

          case 'Certificate':
            if (($dashboard_result[14]->chart_status == 1 || $dashboard_result[15]->chart_status == 1)) {
              $chart_output['certificate'] = $pie_chart;
            }
            break;

          case 'Training Gap Score':
            if ($dashboard_result[16]->chart_status == 1) {
              $chart_output['training_gap_score'] = $pie_chart;
            }
            break;

          case 'Training Gap Count':
            if ($dashboard_result[17]->chart_status == 1) {
              $chart_output['training_gap_count'] = $pie_chart;
            }
            break;

          default:
        }

      }

      $form = \Drupal::formBuilder()->getForm(\Drupal\matrics_dashboard\Form\DashboardFilterForm::class);
      $render = \Drupal::service('renderer');
      $login_form = $render->render($form);
      $roles = \Drupal::currentUser()->getRoles();
      $check['check'] = 1;
      if (in_array('tms_admins_', $roles)) {
        $check['check'] = 0;
      }

      $title_config = \Drupal::configFactory()->getEditable('matrics_dashboard.title_settings');
      if ($title_config) {
        $user_dashboard_title = !empty($title_config->get('my_dashboard_' . $uid)) ? $title_config->get('my_dashboard_' . $uid)
        : t('Your personalised dashboard');
      }
      else {
        $user_dashboard_title = t('Your personalised dashboard');
      }

      $change_title = Link::createFromRoute(t('Change title'), 'matrics_dashboard.dashboard_personalize_title', [], [
        'absolute' => TRUE,
        'attributes' => [
          'class' => ['use-ajax'],
          'style' => 'float:none',
          'data-dialog-type' => 'modal',
        ],
      ]);

      return [
        '#theme' => 'dashboard_manager',
        '#dashboard_title' => $user_dashboard_title,
        '#dashboard_title_url' => $change_title,
        '#uroles' => $check,
        '#items' => $position,
        '#blocks' => $login_form,
        '#chart' => $chart_output,
        '#tile_status' => $dashboard_result,
        '#attached' => [
          'library' => [
            'matrics_dashboard/matrics_styles',
          ],
        ],
        '#cache' => ['max-age' => 0],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxblock() {
    $uid = \Drupal::currentUser()->id();
    $position = \Drupal::request()->request->get('position');
    $db = $this->database;
    $db->update('drag_block')
      ->fields([
        'block_position' => serialize($position),
      ])
      ->condition('uid', $uid, '=')
      ->execute();
    echo $position; die;
    return "success";
  }

}
