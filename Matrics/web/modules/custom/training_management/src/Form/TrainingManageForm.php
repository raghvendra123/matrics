<?php

namespace Drupal\training_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\charts\Services\ChartsSettingsServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class TrainingManageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'training_manage_form';
  }

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
   *   The chart setting service.
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
    $container->get('charts.settings'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['container2'] = load_all_rows();
    $connection = \Drupal::database();
    $customerid = get_session_customerid();
    $query = $connection->select('user__field_certificates ', 'c');
    $query->fields('c', ['entity_id', 'field_certificates_target_id']);
    $query->join('user__roles', 'r', 'c.entity_id = r.entity_id');
    $query->condition('r.roles_target_id', 'individual_employee');
    if (!empty($customerid)) {
      $query->join('user__field_customer', 'cu', 'c.entity_id = cu.entity_id');
      $query->condition('cu.field_customer_target_id', $customerid);
    }
    $query->join('paragraph__field_expiry_date', 'd', 'd.entity_id = c.field_certificates_target_id');
    $query->join('paragraph__field_certificate_type', 't', 't.entity_id = d.entity_id');
    $query->fields('t', ['field_certificate_type_target_id']);
    $query->join('user__field_job_title', 'j', 'j.entity_id = c.entity_id');
    $query->fields('d', ['field_expiry_date_value']);
    $query->join('paragraph__field_enable', 'e', 'e.entity_id = c.field_certificates_target_id');
    $query->fields('e', ['field_enable_value']);
    $query->condition('e.field_enable_value', 1);
    $user_certificate = $query->execute()->fetchAll();
    $six_plus = 0;
    $six_to_three = 0;
    $three_to_one = 0;
    $one_month = 0;
    $expired = 0;

    $total_cert_count = 0;
    $expired_to_be_action = 0;
    $expired_action = 0;
    $one_month_to_be_action = 0;
    $one_month_action = 0;
    $six_to_one_to_be_action = 0;
    $six_to_one_action = 0;
    $six_to_three_to_be_action = 0;
    $six_to_three_action = 0;
    $three_to_one_to_be_action = 0;
    $three_to_one_action = 0;
    foreach ($user_certificate as $certificate) {
      $field_expiry_date = strtotime($certificate->field_expiry_date_value);
      $current_date = date('Y-m-d');
      $current_date = strtotime($current_date);
      $query2 = \Drupal::database()->select('user__field_course_details', 'n')
        ->fields('n', ['field_course_details_target_id'])
        ->condition('n.entity_id', $certificate->entity_id);
      $query2->join('paragraph__field_certificate_type', 'c', 'c.entity_id = n.field_course_details_target_id');
      $query2->condition('c.field_certificate_type_target_id', $certificate->field_certificate_type_target_id);
      $query2->join('paragraph__field_status', 's', 's.entity_id = n.field_course_details_target_id');
      $query2->condition('s.field_status_value', 'completed');
      $query2->join('paragraph__field_enable', 'e', 'e.entity_id = n.field_course_details_target_id');
      $query2->condition('e.field_enable_value', 1);
      $query2->join('paragraph__field_close_booking', 'b', 'b.entity_id = n.field_course_details_target_id');
      $query2->condition('b.field_close_booking_value', 1);
      $close_booking = $query2->execute()->fetchAll();
      // $query3 = \Drupal::database()->select('node_field_data', 'n')
      //  ->fields('n', ['nid'])
      //  ->condition('n.nid', $certificate->field_certificate_type_target_id);
      // $node_id_exist = $query3->execute()->fetchAll();
      if (empty($close_booking)) {
        if ($field_expiry_date < $current_date) {
          $expired++;
          $total_cert_count++;
          $query2 = \Drupal::database()->select('user__field_course_details', 'n')
            ->fields('n', ['field_course_details_target_id'])
            ->condition('n.entity_id', $certificate->entity_id);
          $query2->join('paragraph__field_certificate_type', 'c', 'c.entity_id = n.field_course_details_target_id');
          $query2->condition('c.field_certificate_type_target_id', $certificate->field_certificate_type_target_id);
          $query2->join('paragraph__field_enable', 'e', 'e.entity_id = n.field_course_details_target_id');
          $query2->condition('e.field_enable_value', 1);
          $query2->join('paragraph__field_status', 's', 's.entity_id = n.field_course_details_target_id');
          $query2->condition('s.field_status_value', '', '!=');
          $status = $query2->execute()->fetchAll();

          if (empty($status)) {
            $expired_to_be_action++;
          }
          else {
            $expired_action++;
          }
        }
        elseif ($field_expiry_date > $current_date) {
          $diff = abs($field_expiry_date - $current_date);
          $years = round($diff / 60 / 60 / 24 / 365);
          $months = ($diff / 60 / 60 / 24 / 30);
          if ($months > 6 || $years != 0) {
            $six_plus++;
          }
          elseif ($months <= 6 && $months > 3) {
            $six_to_three++;
            $total_cert_count++;

            $query2 = \Drupal::database()->select('user__field_course_details', 'n')
              ->fields('n', ['field_course_details_target_id'])
              ->condition('n.entity_id', $certificate->entity_id);
            $query2->join('paragraph__field_certificate_type', 'c', 'c.entity_id = n.field_course_details_target_id');
            $query2->condition('c.field_certificate_type_target_id', $certificate->field_certificate_type_target_id);
            $query2->join('paragraph__field_enable', 'e', 'e.entity_id = n.field_course_details_target_id');
            $query2->condition('e.field_enable_value', 1);
            $query2->join('paragraph__field_status', 's', 's.entity_id = n.field_course_details_target_id');
            $query2->condition('s.field_status_value', '', '!=');
            $status = $query2->execute()->fetchAll();

            if (empty($status)) {
              $six_to_three_to_be_action++;
            }
            else {
              $six_to_three_action++;
            }
          }
          elseif ($months <= 3 && $months > 1) {
            $three_to_one++;
            $total_cert_count++;

            $query2 = \Drupal::database()->select('user__field_course_details', 'n')
              ->fields('n', ['field_course_details_target_id'])
              ->condition('n.entity_id', $certificate->entity_id);
            $query2->join('paragraph__field_certificate_type', 'c', 'c.entity_id = n.field_course_details_target_id');
            $query2->condition('c.field_certificate_type_target_id', $certificate->field_certificate_type_target_id);
            $query2->join('paragraph__field_enable', 'e', 'e.entity_id = n.field_course_details_target_id');
            $query2->condition('e.field_enable_value', 1);
            $query2->join('paragraph__field_status', 's', 's.entity_id = n.field_course_details_target_id');
            $query2->condition('s.field_status_value', '', '!=');
            $status = $query2->execute()->fetchAll();

            if (empty($status)) {
              $three_to_one_to_be_action++;
            }
            else {
              $three_to_one_action++;
            }
          }
          else {
            $one_month++;
            $total_cert_count++;

            $query2 = \Drupal::database()->select('user__field_course_details', 'n')
              ->fields('n', ['field_course_details_target_id'])
              ->condition('n.entity_id', $certificate->entity_id);
            $query2->join('paragraph__field_certificate_type', 'c', 'c.entity_id = n.field_course_details_target_id');
            $query2->condition('c.field_certificate_type_target_id', $certificate->field_certificate_type_target_id);
            $query2->join('paragraph__field_enable', 'e', 'e.entity_id = n.field_course_details_target_id');
            $query2->condition('e.field_enable_value', 1);
            $status = $query2->execute()->fetchAll();

            if (empty($status)) {
              $one_month_to_be_action++;
            }
            else {
              $one_month_action++;
            }
          }
        }
      }
    }
    $output['status']['expired'] = $expired;
    $output['status']['one_month'] = $one_month;
    $output['status']['three_to_one'] = $three_to_one;
    $output['status']['six_to_three'] = $six_to_three;
    // $output['status']['six_to_one'] = $six_to_one;
    // $output['status']['six_plus'] = $six_plus;
    $options = [
      'expired' => 'expired',
      'one_month' => 'Within 1 month',
      // 'six_to_one' => 'Within 6 to 1 month',
      'three_to_one' => 'Within 3 to 1 month',
      'six_to_three' => 'Within 6 to 3 month',
    ];
    if (!empty($output['status'])) {
      $form['certificate_count'] = [
        '#type' => 'select2',
        '#options' => $options,
        '#title' => 'Expiry Type',
        '#required' => 'required',
        '#multiple' => TRUE,
        '#ajax' => [
          'callback' => '::training_manage',
          'wrapper' => 'box-contain',
        ],
        '#attributes' => ['class' => ['col-md-6']],
      ];
    }
    $count = 0;
    $widget = '';
    foreach ($output['status'] as $key => $val) {
      switch ($key) {
        case "expired":
          $class = 'expired';
          $title = 'Expired';
          $action = $expired_action;
          $to_be_action = $expired_to_be_action;
          break;

        case "one_month":
          $class = 'one_month';
          $title = 'Within 1 month';
          $action = $one_month_action;
          $to_be_action = $one_month_to_be_action;
          break;

        case "three_to_one":
          $class = 'three_to_one';
          $title = 'Within 3 to 1 Month';
          $action = $three_to_one_action;
          $to_be_action = $three_to_one_to_be_action;
          break;

        case "six_to_three":
          $class = 'six_to_three';
          $title = 'Within 6 to 3 month';
          $action = $six_to_three_action;
          $to_be_action = $six_to_three_to_be_action;
          break;

        default:
      }
      $count = $count + 1;
      $percent = 0;
      if ($val != 0) {
        $percent = ($action * 100) / $val;
      }
      $widget .= '<div class="col-xl-2 col-md-6" >
        
        <p class="row_title" rel="' . $class . '"  ><strong>' . $title . ': ' . $val . '</strong></p>
        <p class="row_title">Actioned: <strong>' . $action . '</strong> </p>
        <p>To be Actioned: <strong>' . $to_be_action . '</strong> </p>
        <div class="progress">
            <div class="progress-bar ' . $class . '" role="progressbar" aria-valuenow="' . $action . '" aria-valuemin="0" aria-valuemax="' . $val . '" width-percent="' . $percent . '">
              <span class="sr-only"></span>
            </div>
        </div>
        <p>Total: ' . $total_cert_count . '</p>   
            
      </div>';
    }
    $form['cer_count'] = [
      '#type' => 'markup',
      '#title' => 'Count',
      '#markup' => $widget,
    ];
    $customerid = get_session_customerid();
    $option = get_assets();
    $form['asset'] = [
      '#type' => 'select',
      '#options' => $option,
      '#title' => 'Asset',
      '#required' => 'required',
      // '#multiple' => TRUE,
      '#ajax' => [
        'callback' => '::assetDropdownCallback',
        'wrapper' => 'asset-fieldset-container',
      ],
      '#attributes' => ['class' => ['col-md-6']],
    ];
    if (empty($form_state->getValue('asset'))) {
      $selected_family = '';
    }
    else {
      $selected_family = $form_state->getValue('asset');
    }
    $form['asset_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asset-fieldset-container'],
    ];
    $form['asset_fieldset_container']['job_title'] = [
      '#type' => 'select2',
      '#title' => 'Job Title',
      '#options' => get_jobtitle($selected_family),
      '#multiple' => TRUE,
      '#ajax' => [
        'callback' => '::training_manage',
        'wrapper' => 'box-contain',
      ],
      /* '#states' => [
        'visible' => [
          [':input[name="asset"]' => ['!value' => '']],
        ],
      ], */
    ];
    $connection = \Drupal::database();
    $query = $connection->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'title']);
    $query->condition('n.type', 'vendor');
    if (!empty($customerid)) {
      $query->join('node__field_customer', 'cu', 'n.nid = cu.entity_id');
      $query->condition('cu.field_customer_target_id', $customerid);
    }
    $result = $query->execute()->fetchAll();
    foreach ($result as $value) {
      $option1[$value->nid] = $value->title;
    }
    $form['training_provider'] = [
      '#type' => 'select2',
      '#options' => $option1,
      '#title' => 'Training Provider',
      '#required' => 'required',
      '#multiple' => TRUE,
      '#ajax' => [
        'callback' => '::training_manage',
        'wrapper' => 'box-contain',
      ],
    ];

    $form['course_booked'] = [
      '#type' => 'select',
      '#options' => [
        'to be actioned' => 'To be actioned',
        'course booked' => 'Course booked',
        'completed' => 'Course Completed',
        'cancelled' => 'Cancelled',
        'failed' => 'Failed',
        'in-progress' => 'In-progress',
        'not required' => 'Not required',
      ],
      '#title' => 'Booking Status',
      '#required' => 'required',
      '#ajax' => [
        'callback' => '::training_manage',
        'wrapper' => 'box-contain',
      ],
    ];

    $form['accreditation'] = [
      '#type' => 'select2',
      '#options' => [
        'OPITO' => 'OPITO',
        'GWO' => 'GWO',
        'IWCF' => 'IWCF',
        'STCW' => 'STCW',
        'MCA' => 'MCA',
      ],
      '#title' => 'Accreditation',
      '#multiple' => TRUE,
      '#required' => 'required',
      '#ajax' => [
        'callback' => '::training_manage',
        'wrapper' => 'box-contain',
      ],
    ];

    $employee = get_employee_list();

    $form['employee'] = [
      '#type' => 'select2',
      '#options' => $employee,
      '#title' => 'Employee Name',
      '#required' => 'required',
      '#multiple' => TRUE,
      '#ajax' => [
        'callback' => '::training_manage',
        'wrapper' => 'box-contain',
      ],
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => 'Expiry Date',
      '#required' => 'required',
      '#default_value' => date('m/d/Y'),
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => 'to',
      '#required' => 'required',
      '#ajax' => [
        'callback' => '::training_manage',
        'wrapper' => 'box-contain',
      ],
    ];

    $form['cer_start_date'] = [
      '#type' => 'date',
      '#title' => 'Start Date',
      '#required' => 'required',
      '#default_value' => date('m/d/Y'),
    ];

    $form['cer_end_date'] = [
      '#type' => 'date',
      '#title' => 'to',
      '#required' => 'required',
      '#ajax' => [
        'callback' => '::training_manage',
        'wrapper' => 'box-contain',
      ],
    ];
    $form['container']['contain'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'box-contain', 'class' => 'row'],
      '#weight' => '9',
    ];
    $form['container']['contain']['box'] = [
      '#type' => 'markup',
      '#markup' => '',
      // '#attributes' => ['class' => 'col-md-6'],
      '#weight' => '2',
    ];
    $form['#attached']['library'][] = 'training_management/training-management';
    $form['#theme'] = 'training_form_manage';
    return $form;
  }

  /**
   * Ajax callback for the color dropdown.
   */
  public function training_manage(array $form, FormStateInterface $form_state) {
    $customerid = get_session_customerid();
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
    if (!empty($form_state->getValue('asset'))) {
      $query->condition('n.nid', $form_state->getValue('asset'), 'IN');
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
      if (!empty($form_state->getValue('job_title'))) {
        if (!in_array("all", $form_state->getValue('job_title'))) {
          $query1->condition('n.nid', $form_state->getValue('job_title'), 'IN');
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
        if (!empty($customerid)) {
          $query2->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query2->fields('cu', ['field_customer_target_id']);
          $query2->condition('cu.field_customer_target_id', $customerid);
        }
        if (!empty($form_state->getValue('employee'))) {
          $query2->join('user__field_employee_name', 'f', 'u.uid = f.entity_id');
          $query2->fields('f', ['field_employee_name_value']);
          $query2->condition('f.entity_id', $form_state->getValue('employee'), 'IN');
        }

        if (!empty($form_state->getValue('training_provider')) || !empty($form_state->getValue('accreditation')) || !empty($form_state->getValue('course_booked'))) {
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
          if (!empty($form_state->getValue('training_provider'))) {
            $query2->join('node_field_data', 'nai', 'fa.field_provider_name_target_id = nai.nid');
            $query2->addField('nai', 'title', 'provider_title');
            $query2->condition('fa.field_provider_name_target_id', $form_state->getValue('training_provider'), 'IN');
          }
          if (!empty($form_state->getValue('accreditation'))) {
            // $query2->fields('pc', ['field_course_name_target_id']);
            $query2->join('node_field_data', 'n', 'n.nid = pc.field_course_name_target_id');
            $query2->fields('n', ['title']);
            $query2->join('node__field_provider_details', 'd', 'd.entity_id = n.nid');
            // $query2->fields('d', ['field_provider_details_target_id']);
            $query2->join('paragraph__field_provider_name', 'p', 'p.entity_id = d.field_provider_details_target_id');
            $query2->addField('p', 'field_provider_name_target_id', 'name');
            $query2->join('paragraph__field_accreditation', 'a', 'a.entity_id = d.field_provider_details_target_id');
            $query2->fields('a', ['field_accreditation_value']);
            $query2->condition('a.field_accreditation_value', $form_state->getValue('accreditation'), 'IN');
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
            $query2->join('paragraph__field_enable', 'e', 'e.entity_id = c.field_course_details_target_id');
            $query2->condition('e.field_enable_value', 1);
          }
          if (!empty($form_state->getValue('course_booked')) && $form_state->getValue('course_booked') == 'to be actioned') {
            $query2->leftJoin('user__field_course_details', 'c', 'c.entity_id = u.uid');
            $query2->fields('c', ['field_course_details_target_id']);
            $query2->leftJoin('paragraph__field_certificate_type', 'se', 'se.entity_id = c.field_course_details_target_id');
            $query2->fields('se', ['field_certificate_type_target_id']);
            $query2->leftJoin('paragraph__field_enable', 'e', 'e.entity_id = c.field_course_details_target_id');
            $query2->fields('e', ['field_enable_value']);
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
          if (!empty($form_state->getValue('accreditation'))) {
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
              $current_date = date('Y-m-d');
              $current_date = strtotime($current_date);
              $field_expiry_date = isset($user_certificate[$certi]) ? strtotime($user_certificate[$certi]) : 0;
              $field_start_date = isset($user_certificate[$certi . '2']) ? strtotime($user_certificate[$certi . '2']) : 0;
            //   $start_time = isset($user_certificate[$certi . '2']) ? new DrupalDateTime($user_certificate[$certi . '2']) : 0;
            //   $start_timestamp = !empty($start_time) ? $start_time->getTimestamp() : 0;
            //   $expirty_time = isset($user_certificate[$certi]) ? new DrupalDateTime($user_certificate[$certi]) : 0;
            //   $expiry_timestamp = !empty($expirty_time) ? $expirty_time->getTimestamp() : 0;
              $c_date = strtotime(date('Y-m-d'));
              $c_days = (($field_expiry_date - $c_date) / (1000 * 3600 * 24)) * 1000;
              $count_days = $current_date < $field_expiry_date ? $c_days : 0;
              $condition = in_array($certi, $certificate_list);
              if (!empty($form_state->getValue('training_provider')) || !empty($form_state->getValue('accreditation')) || !empty($form_state->getValue('course_booked'))) {
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
                $query->join('paragraph__field_enable', 'e', 'e.entity_id = c.field_course_details_target_id');
                $query->condition('e.field_enable_value', 1);
                $query->join('paragraph__field_certificate_type', 'ct', 'ct.entity_id = c.field_course_details_target_id');
                $query->join('node_field_data', 'nd', 'nd.nid = ct.field_certificate_type_target_id');
                $query->condition('nd.title', $certi);
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

                if (!empty($form_state->getValue('start_date'))) {
                  $start_date = strtotime($form_state->getValue('start_date'));
                }
                if (!empty($form_state->getValue('end_date'))) {
                  $end_date = strtotime($form_state->getValue('end_date'));
                }
                if (!empty($form_state->getValue('cer_start_date'))) {
                  $cer_start_date = strtotime($form_state->getValue('cer_start_date'));
                }
                if (!empty($form_state->getValue('cer_end_date'))) {
                  $cer_end_date = strtotime($form_state->getValue('cer_end_date'));
                }
                $diff = abs($field_expiry_date - $current_date);
                $years = round($diff / 60 / 60 / 24 / 365);
                $months = ($diff / 60 / 60 / 24 / 30);
                if (!empty($user_course_booking)) {
                  if (!empty($form_state->getValue('start_date')) && !empty($form_state->getValue('end_date')) && !empty($form_state->getValue('cer_start_date')) && !empty($form_state->getValue('cer_end_date'))) {
                    if ($field_expiry_date > $start_date && $field_expiry_date < $end_date && $field_start_date > $cer_start_date && $field_start_date < $cer_end_date) {

                      if (!empty($form_state->getValue('certificate_count'))) {
                        $cer_count = $form_state->getValue('certificate_count');
                        if (in_array('expired', $cer_count)) {
                          if ($field_expiry_date < $current_date && !empty($field_expiry_date)) {
                            $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                            $row[$row_key]['name'] = $username;
                            $row[$row_key]['last_name'] = get_last_name($user->uid);
                            $row[$row_key]['asset'] = $value->title;
                            $row[$row_key]['job_title'] = $job->title;
                            $row[$row_key]['uid'] = $user->uid;
                            $row[$row_key]['date'] = $user_certificate[$certi];
                            $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                            $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                          $row[$row_key]['date'] = $user_certificate[$certi];
                          $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                          $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                  elseif (!empty($form_state->getValue('start_date')) && !empty($form_state->getValue('end_date'))) {
                    if ($field_expiry_date > $start_date && $field_expiry_date < $end_date) {
                      if (!empty($form_state->getValue('certificate_count'))) {
                        $cer_count = $form_state->getValue('certificate_count');
                        if (in_array('expired', $cer_count)) {
                          if ($field_expiry_date < $current_date && !empty($field_expiry_date)) {
                            $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                            $row[$row_key]['name'] = $username;
                            $row[$row_key]['last_name'] = get_last_name($user->uid);
                            $row[$row_key]['asset'] = $value->title;
                            $row[$row_key]['job_title'] = $job->title;
                            $row[$row_key]['uid'] = $user->uid;
                            $row[$row_key]['date'] = $user_certificate[$certi];
                            $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                            $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                          $row[$row_key]['date'] = $user_certificate[$certi];
                          $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                          $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                  elseif (!empty($form_state->getValue('cer_start_date')) && !empty($form_state->getValue('cer_end_date'))) {
                    if ($field_start_date > $cer_start_date && $field_start_date < $cer_end_date) {
                      if (!empty($form_state->getValue('certificate_count'))) {
                        $cer_count = $form_state->getValue('certificate_count');
                        if (in_array('expired', $cer_count)) {
                          if ($field_expiry_date < $current_date && !empty($field_expiry_date)) {
                            $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                            $row[$row_key]['name'] = $username;
                            $row[$row_key]['last_name'] = get_last_name($user->uid);
                            $row[$row_key]['asset'] = $value->title;
                            $row[$row_key]['job_title'] = $job->title;
                            $row[$row_key]['uid'] = $user->uid;
                            $row[$row_key]['date'] = $user_certificate[$certi];
                            $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                            $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                              $row[$row_key]['date'] = $user_certificate[$certi];
                              $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                              $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                          $row[$row_key]['date'] = $user_certificate[$certi];
                          $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                          $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                    if (!empty($form_state->getValue('certificate_count'))) {
                      $cer_count = $form_state->getValue('certificate_count');
                      if (in_array('expired', $cer_count)) {
                        if ($field_expiry_date < $current_date && !empty($field_expiry_date)) {
                          $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                          $row[$row_key]['name'] = $username;
                          $row[$row_key]['last_name'] = get_last_name($user->uid);
                          $row[$row_key]['asset'] = $value->title;
                          $row[$row_key]['job_title'] = $job->title;
                          $row[$row_key]['uid'] = $user->uid;
                          $row[$row_key]['date'] = $user_certificate[$certi];
                          $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                          $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                            $row[$row_key]['date'] = $user_certificate[$certi];
                            $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                            $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                            $row[$row_key]['date'] = $user_certificate[$certi];
                            $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                            $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                            $row[$row_key]['date'] = $user_certificate[$certi];
                            $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                            $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
                        $row[$row_key]['date'] = $user_certificate[$certi];
                        $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                        $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
    $element = $form['container'];
    $element['contain']['box']['table'] = [
      '#theme' => 'training_manage',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('excel_file') == NULL) {
      $form_state->setErrorByName('excel_file', $this->t('upload proper File'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function assetDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['asset_fieldset_container'];
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
    if (!empty($key)) {
      $options[''] = '- select -';
      $options['all'] = 'Select all';
      $customerid = get_session_customerid();
      $query1 = \Drupal::database()->select('node__field_asset', 'fa')->fields('fa', ['entity_id'])->condition('fa.field_asset_target_id', $key)->condition('fa.bundle', 'job_title');
      $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
      if (!empty($customerid)) {
        $query1->join('node__field_customer', 'cu', 'n.nid = cu.entity_id');
        $query1->condition('cu.field_customer_target_id', $customerid);
      }
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
  }

}

/**
 * Default callback.
 */
function load_all_rows() {
  $customerid = get_session_customerid();
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
      if (!empty($customerid)) {
        $query2->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
        $query2->fields('cu', ['field_customer_target_id']);
        $query2->condition('cu.field_customer_target_id', $customerid);
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
      foreach ($job_user as $user) {
        $row = [];
        $certificate_list = '';
        $certificate_list = list_certificate($user->uid);
        $user_certificate = certificate_expiration_date($user->uid);
        $current_date = date('Y-m-d');
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
              $query->join('paragraph__field_enable', 'e', 'e.entity_id = c.field_course_details_target_id');
              $query->condition('e.field_enable_value', 1);
              $query->join('paragraph__field_certificate_type', 'ct', 'ct.entity_id = c.field_course_details_target_id');
              $query->join('node_field_data', 'nd', 'nd.nid = ct.field_certificate_type_target_id');
              $query->condition('nd.title', $certi);
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
                $months = ($diff / 60 / 60 / 24 / 30);
                // $start_time = isset($user_certificate[$certi . '2']) ? new DrupalDateTime($user_certificate[$certi . '2']) : 0;
                // $start_timestamp = $start_time->getTimestamp();
                // $expirty_time = isset($user_certificate[$certi]) ? new DrupalDateTime($user_certificate[$certi]) : 0;
                // $expiry_timestamp = $expirty_time->getTimestamp();
                $c_date = strtotime(date('Y-m-d'));
                $c_days = (($field_expiry_date - $c_date) / (1000 * 3600 * 24)) * 1000;
                $count_days = $current_date < $field_expiry_date ? $c_days : 0;
                $row[$row_key]['cid'] = $user_certificate[$certi . '3'];
                $row[$row_key]['name'] = get_first_name($user->uid);
                $row[$row_key]['last_name'] = get_last_name($user->uid);
                $row[$row_key]['asset'] = $value->title;
                $row[$row_key]['job_title'] = $job->title;
                $row[$row_key]['uid'] = $user->uid;
                $row[$row_key]['date'] = $user_certificate[$certi];
                $row[$row_key]['date_str'] = strtotime($user_certificate[$certi]);
                $row[$row_key]['counts'] = ' (' . $count_days . ' Days)';
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
  $form['container'] = [];
  $element = $form['container'];
  $element['contain']['box']['table'] = [
    '#theme' => 'training_manage',
    '#items' => $output,
    '#cache' => ['max-age' => 0],
  ];
  return $element;
}
