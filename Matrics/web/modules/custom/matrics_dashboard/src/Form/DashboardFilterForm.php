<?php

namespace Drupal\matrics_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\charts\Services\ChartsSettingsServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Views;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Locale\CountryManager;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class DashboardFilterForm extends FormBase {

  /**
   * The charts settings.
   *
   * @var \Drupal\charts\Services\ChartsSettingsServiceInterface
   */
  protected $chartSettings;

  /**
   * Construct.
   *
   * @param \Drupal\charts\Services\ChartsSettingsServiceInterface $chartSettings
   *   The chart settings.
   */
  public function __construct(ChartsSettingsServiceInterface $chartSettings) {
    $this->chartSettings = $chartSettings->getChartsSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('charts.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dashboard_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $pid = NULL) {
    $connection = \Drupal::database();
    $customerid = get_session_customerid();
    $option = get_assets();
    
    $form['asset'] = [
      '#type' => 'select2',
      '#options' => $option,
      '#title' => 'Asset',
      '#ajax' => [
        'callback' => '::list_employee',
        'wrapper' => 'box-content',
      ],
      '#prefix' => '<div class="col-md-2">',
      '#suffix' => '</div>',
      '#multiple' => TRUE,
    ];

    if (empty($form_state->getValue('asset'))) {
      $selected_family = '';
    }
    else {
      $selected_family = $form_state->getValue('asset');
    }

    $query1 = \Drupal::database()->select('node__field_asset', 'fa')
      ->fields('fa', ['entity_id', 'field_asset_target_id'])
      ->condition('fa.bundle', 'job_title');
    $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
    $query1->join('node_field_data', 'a', 'a.nid = fa.field_asset_target_id');
    $query1->fields('n', ['title']);
    $query1->fields('a', ['title']);

    if (!empty($customerid)) {
      $query1->join('node__field_customer', 'cu', 'fa.entity_id = cu.entity_id');
      $query1->fields('cu', ['field_customer_target_id']);
      $query1->condition('cu.bundle', 'job_title');
      $query1->condition('cu.field_customer_target_id', $customerid);
      // dump($customerid);
    }
    $job_title = $query1->execute()->fetchAll();
    $result1 = [];
    foreach ($job_title as $element) {
      $result1[$element->a_title][] = $element;
    }

    foreach ($result1 as $key1 => $value1) {
      $option1[$key1] = $key1;
      foreach ($value1 as $val) {
        $option1[$val->entity_id] = ' - ' . $val->title;
      }
    }

    $form['asset_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asset-fieldset-container'],
    ];

    $form['asset_fieldset_container']['job_title'] = [
      '#type' => 'select2',
      '#title' => 'Job Title',
      '#options' => $option1,
      '#ajax' => [
        'callback' => '::list_employee',
        'wrapper' => 'box-content',
      ],
      '#prefix' => '<div class="col-md-2">',
      '#suffix' => '</div>',
      '#default_value' => 'select',
      '#multiple' => TRUE,
    ];

    // $country[''] = '- Select -';.
    $countries = CountryManager::getStandardList();
    foreach ($countries as $key => $value) {
      $country[(string) $key] = (string) $value;
    }

    $form['region'] = [
      '#type' => 'select2',
      '#options' => $country,
      '#title' => 'Region',
      '#prefix' => '<div class="col-md-2">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::list_employee',
        'wrapper' => 'box-content',
      ],
      '#multiple' => TRUE,
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => 'Start Date',
      '#prefix' => '<div class="col-md-2">',
      '#suffix' => '</div>',
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => 'End Date',
      '#prefix' => '<div class="col-md-2">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::list_employee',
        'wrapper' => 'box-content',
      ],
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function assetDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['asset_fieldset_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function list_employee(array &$form, FormStateInterface $form_state) {
    $db = \Drupal::database();
    $dashboard = $db->select('dashboard_manage', 'u');
    $dashboard->fields('u', []);
    $dashboard_result = $dashboard->execute()->fetchAll();
    $asset_id = NULL;
    if (!empty($form_state->getValue('asset'))) {
      $asset_id = $form_state->getValue('asset');
    }
    $job_title_id = NULL;
    if (!empty($form_state->getValue('job_title'))) {
      $job_title_id = $form_state->getValue('job_title');
    }
    $region = NULL;
    if (!empty($form_state->getValue('region'))) {
      $region = $form_state->getValue('region');
    }
    $start_date = NULL;
    if (!empty($form_state->getValue('start_date'))) {
      $start_date = $form_state->getValue('start_date');
    }
    $end_date = NULL;
    if (!empty($form_state->getValue('end_date'))) {
      $end_date = $form_state->getValue('end_date');
    }
    $position['course_booked'] = get_booking_count('course booked', NULL, $asset_id, $job_title_id, $region, $start_date, $end_date);
    $position['complete_count'] = get_booking_count('completed', NULL, $asset_id, $job_title_id, $region, $start_date, $end_date);
    $position['cacelled_count_customer'] = get_booking_count('cancelled', 'Cancelled by Customer', $asset_id, $job_title_id, $region, $start_date, $end_date);
    $position['cacelled_count_provider'] = get_booking_count('cancelled', 'Cancelled by provider', $asset_id, $job_title_id, $region, $start_date, $end_date);
    $position['cmt_fee_courses'] = get_course_fee_sum('node__field_cm', 'field_cm_value');
    $position['customer_fee_courses'] = get_course_fee_sum('node__field_customer_fee', 'field_customer_fee_value');
    $position['employees_registered'] = get_employee_count($asset_id, $job_title_id, $region);
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
    $position['course_duration'] = get_course_result_days($asset_id, $job_title_id, $region, $start_date, $end_date);

    // Certificate count.
    $position['certificate_issued'] = get_all_certificate_count($asset_id, $job_title_id, $region, $start_date, $end_date);

    // Training gap count.
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
    $position['course_passed'] = get_course_result('passed', $asset_id, $job_title_id, $region, $start_date, $end_date);
    $position['course_failed'] = get_course_result('failed', $asset_id, $job_title_id, $region, $start_date, $end_date);
    $position['course_resit'] = get_course_result('resit', $asset_id, $job_title_id, $region, $start_date, $end_date);

    $library = $this->chartSettings['library'];
    if (empty($library)) {
      $this->messenger->addError($this->t('You need to first configure Charts default settings'));
      return [];
    }

    $library = $this->chartSettings['library'];
    if (empty($library)) {
      $this->messenger->addError($this->t('You need to first configure Charts default settings'));
      return [];
    }

    $all_chart = ['Booking', 'Booking Cancel', 'Total Spend', 'Training Days',
      'Vendors', 'Courses', 'Employees', 'Certificate', 'Training Gap Score', 'Training Gap Count',
    ];
    $chart_output = [];
    foreach ($all_chart as $all) {
      $pie_chart = '';
      $seriesData = [];
      $options = [];
      if ($all == 'Booking' || $all == 'Booking Cancel' || $all == 'Total Spend' || $all == 'Vendors' || $all == 'Courses' || $all == 'Certificate') {
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
              'name' => 'Bookings in progress',
              'color' => '#C6EFCE',
              'type' => $this->chartSettings['type'],
              'data' => [$position['inprogress_count']],
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
              'data' => [32],
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
              'data' => [20, 15, 10, 8, 7, 6],
            ];
          }
          break;

        case 'Vendors':
          $seriesData[] = [
            'name' => 'Vendors used',
            'color' => '#FFEB9A',
            'type' => 'column',
            'data' => ['32'],
          ];
          $seriesData[] = [
            'name' => 'Registered vendors',
            'color' => '#FFC7CB',
            'type' => 'column',
            'data' => ['32'],
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
          $seriesData[] = [
            'name' => 'Courses resit',
            'color' => '#FFC7CB',
            'type' => 'column',
            'data' => [$position['course_resit']],
          ];
          break;

        case 'Employees':
          $months = [];
          $currentMonth = (int) date('m', '-6 month');

          for ($x = $currentMonth; $x < $currentMonth + 6; $x++) {
            $months[] = date('M', mktime(0, 0, 0, $x, 1));
          }
          $categories = $months;
          $seriesData[] = [
            'name' => 'Number of training days',
            'color' => '#000000',
            'type' => 'column',
            'data' => [14, 12, 10, 0, 0, 0],
          ];
          break;

        case 'Certificate':
          $seriesData[] = [
            'name' => 'Certificates issued',
            'color' => '#FFEB9A',
            'type' => 'column',
            'data' => ['32'],
          ];
          $seriesData[] = [
            'name' => 'Certificates pending',
            'color' => '#FFC7CB',
            'type' => 'column',
            'data' => ['32'],
          ];
          break;

        case 'Training Gap Score':
          if ($dashboard_result[7]->chart_status == 1) {
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
              'data' => [20, 15, 10, 8, 7, 6],
            ];
          }
          break;

        case 'Training Gap Count':
          if ($dashboard_result[7]->chart_status == 1) {
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
              'data' => [20, 15, 10, 8, 7, 6],
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

        case 'Vendors':
          if (($dashboard_result[8]->chart_status == 1 || $dashboard_result[9]->chart_status == 1)) {
            $chart_output['vendors'] = $pie_chart;
          }
          break;

        case 'Courses':
          if (($dashboard_result[10]->chart_status == 1 || $dashboard_result[11]->chart_status == 1 || $dashboard_result[12]->chart_status == 1)) {
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

    return [
      '#theme' => 'dashboard_manager',
      '#items' => $position,
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

  /**
   * Helper function to populate the second dropdown.
   *
   * This would normally be pulling data from the database.
   *
   * @param string $key
   *   This will determine which set of options is returned.
   *
   * @return array
   *   Dropdown options
   */
  public static function getSecondDropdownOptions($key = '') {
    $options = [];
    $options['select'] = '- select -';
    if (!empty($key)) {
      $options['all' . $key[0]] = 'Select all';
      $query1 = \Drupal::database()->select('node__field_asset', 'fa')
        ->fields('fa', ['entity_id'])
        ->condition('fa.field_asset_target_id', $key)
        ->condition('fa.bundle', 'job_title');
      $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
      $query1->fields('n', ['title']);
      $job_title = $query1->execute()->fetchAll();

      foreach ($job_title as $job) {
        $options[$job->entity_id] = $job->title;
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $pid = $form_state->getValue('pid');
    $p = Paragraph::load($pid);
    $p->set('field_status', $form_state->getValue('status'));
    $p->set('field_cancelled_by', 'Cancelled by Customer');
    $p->save();
    $uid = \Drupal::currentUser()->id();
    $response = new RedirectResponse('/user/' . $uid);
    $response->send();
  }

}
