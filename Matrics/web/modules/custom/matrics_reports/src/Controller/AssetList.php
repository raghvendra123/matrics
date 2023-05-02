<?php

namespace Drupal\matrics_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\charts\Services\ChartsSettingsServiceInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\node\Entity\Node;

/**
 * An assets controller.
 */
class AssetList extends ControllerBase {

  /**
   * The chart service.
   *
   * @var \Drupal\charts\Services\ChartsSettingsServiceInterface
   *   The charts settings.
   */
  protected $chartSettings;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * Construct.
   *
   * @param \Drupal\charts\Services\ChartsSettingsServiceInterface $chartSettings
   *   The charts settings.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuidService
   *   The UUID service.
   */
  public function __construct(ChartsSettingsServiceInterface $chartSettings, MessengerInterface $messenger, UuidInterface $uuidService) {
    $this->chartSettings = $chartSettings->getChartsSettings();
    $this->messenger = $messenger;
    $this->uuidService = $uuidService;
  }

  /**
   * {@inheritdoc}
   */
  public function listing() {
    $assets = \Drupal::request()->query->get('assets');
    $compliance_score = \Drupal::request()->query->get('compliance_score');
    $compliance_score_query = FALSE;
    if ($compliance_score) {
      $compliance_score_query = TRUE;
    }
    $compliance_score = isset($compliance_score) ? (int) $compliance_score : NULL;
    $form['form'] = $this->formBuilder()->getForm('Drupal\matrics_reports\Form\AssetFilterForm');
    $fields = [];
    $rows = $this->getRowsResult($fields);
    $header = [
      'title' => t('Asset'),
      'content' => t('Asset Compliance Score'),
      'chart' => t('Chart'),
    ];
    $form['asset_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asset-fieldset-container'],
      '#weight' => 100,
    ];
    $form['asset_fieldset_container']['contain']['box']['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No content has been found.'),
      '#cache' => ['max-age' => 0],
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('charts.settings'), $container->get('messenger'), $container->get('uuid'));
  }

  /**
   * {@inheritdoc}
   */
  public function getRowsResult($fields = []) {
    $rows = [];
    $compliance_score_query = FALSE;
    $compliance_score_min = $compliance_score_max = NULL;
    $first_load = FALSE;
    $customerid = get_session_customerid();
    if (!empty($fields)) {
      $assets = $fields['assets'];
      $compliance_score_min = $fields['compliance_score_min'];
      $compliance_score_max = $fields['compliance_score_max'];
      if (!empty($compliance_score_min)) {
        $compliance_score_query = TRUE;
      }
      $compliance_score_min = isset($compliance_score_min) ? (int) $compliance_score_min : NULL;
      $compliance_score_max = isset($compliance_score_max) ? (int) $compliance_score_max : NULL;

      if ($assets) {
        $nodes[] = Node::load($assets);
      }
      else {
        $query = \Drupal::entityQuery('node')->condition('type', 'asset');
        if (!empty($customerid)) {
          $query->condition('field_customer', $customerid);
        }
        $nids = $query->execute();
        $nodes = Node::loadMultiple($nids);
      }
    }
    else {
      $query = \Drupal::entityQuery('node')->condition('type', 'asset');
      if (!empty($customerid)) {
        $query->condition('field_customer', $customerid);
      }
      $nids = $query->execute();
      $nodes = Node::loadMultiple($nids);
      $first_load = TRUE;
    }
    $asset_compliance = 0;
    foreach ($nodes as $key => $value) {
      $per = [];
      $seriesData = [];
      $title = $value->title->value;
      $nid = $value->nid->value;
      $connection = \Drupal::database();
      $query = \Drupal::database()->select('users_field_data', 'u');
      $query->fields('u', ['uid', 'name']);
      $query->condition('u.uid', '1', '!=');
      $query->join('user__field_asset', 'fa', 'fa.entity_id = u.uid');
      $query->condition('fa.field_asset_target_id', $nid, '=');
      $query->fields('fa', ['entity_id']);
      $query->join('user__field_training_gap_score', 'ts', 'ts.entity_id = u.uid');
      $query->fields('ts', ['field_training_gap_score_value']);
      $query->join('user__field_employee_status', 'st', 'st.entity_id = u.uid');
      $query->condition('st.field_employee_status_value', 1, '=');
      $result = $query->execute()->fetchAll();
      $total_compliance = 0;
      $asset_compliance = 0;
      $total_emp = 0;
      $months = [];
      $currentMonth = (int) date('m');
      $field_training_gap_score_value = 0;
      foreach ($result as $key => $value1) {
        $name = $value1->name;
        $uid = $value1->uid;
        $asset_comp = new AssetCompliance();
        $certi_results = $asset_comp->complianceScoreCalculation($uid, date('Y-m-d'));
        $field_training_gap_score_value = $certi_results['training_gap_score'];
        $total_emp = $key + 1;
        $total_compliance = $total_compliance + $field_training_gap_score_value;
        $asset_compliance = number_format(($total_compliance / ($total_emp * 100)) * 100);
      }
      $library = $this->chartSettings['library'];
      if (empty($library)) {
        $this->messenger->addError($this->t('You need to first configure Charts default settings'));
      }
      for ($x = $currentMonth; $x < $currentMonth + 12; $x++) {
        $months[] = date('M', mktime(0, 0, 0, $x, 1));
        $date = date('m' . "/" . date('d') . "/" . 'y', mktime(0, 0, 0, $x, 1));
        $per[] = AssetList::forcast_employee($date, $nid);
      }
      $options = [
        'type' => 'line',
        'title' => $this->t('Asset Score'),
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
        'colors' => $this->chartSettings['colors'],
        'min' => $this->chartSettings['min'],
        'max' => $this->chartSettings['max'],
        'yaxis_prefix' => $this->chartSettings['yaxis_prefix'],
        'yaxis_suffix' => $this->chartSettings['yaxis_suffix'],
        'data_markers' => $this->chartSettings['data_markers'],
        'red_from' => $this->chartSettings['red_from'],
        'red_to' => $this->chartSettings['red_to'],
        'yellow_from' => $this->chartSettings['yellow_from'],
        'yellow_to' => $this->chartSettings['yellow_to'],
        'green_from' => $this->chartSettings['green_from'],
        'green_to' => $this->chartSettings['green_to'],
      ];
      // Sample data format.
      $categories = $months;
      $seriesData[] = [
        'name' => 'Asset Score',
        'color' => '#0d233a',
        'type' => 'line',
        'data' => $per,
      ];

      // Creates a UUID for the chart ID.
      $chartId = 'chart-' . $this->uuidService->generate();
      $chart = [
        '#theme' => 'charts_api_example',
        '#library' => (string) $library,
        '#categories' => $categories,
        '#seriesData' => $seriesData,
        '#options' => $options,
        '#id' => $chartId,
        '#override' => [],
      ];
      $asset_compliance = (int) $asset_compliance;
      if (!empty($compliance_score_min) && ($compliance_score_min < $asset_compliance && $compliance_score_max > $asset_compliance)) {
        $rows[] = [
          Markup::create('<div class="icemax-assets"><strong>' . $title . '</strong> <span class="views-field-view-user"><a href="/asset-compliance/' . $nid . '"> View </a></span></div>'), $asset_compliance . "%", render($chart),
        ];
      }
      elseif (!empty($compliance_score_query) && $compliance_score_min == 0 && ($compliance_score_min < $asset_compliance && $compliance_score_max > $asset_compliance)) {
        $rows[] = [
          Markup::create('<div class="icemax-assets"><strong>' . $title . '</strong> <span class="views-field-view-user"><a href="/asset-compliance/' . $nid . '"> View </a></span></div>'), $asset_compliance . "%", render($chart),
        ];
      }
      elseif ($compliance_score_min == 0 && empty($compliance_score_query) && ($compliance_score_max > $asset_compliance)) {
        $rows[] = [
          Markup::create('<div class="icemax-assets"><strong>' . $title . '</strong> <span class="views-field-view-user"><a href="/asset-compliance/' . $nid . '"> View </a></span></div>'), $asset_compliance . "%", render($chart),
        ];
      }
      elseif ($first_load) {
        $rows[] = [
          Markup::create('<div class="icemax-assets"><strong>' . $title . '</strong> <span class="views-field-view-user"><a href="/asset-compliance/' . $nid . '"> View </a></span></div>'), $asset_compliance . "%", render($chart),
        ];
      }
      else {
      }
    }
    return $rows;
  }

  /**
   * {@inheritdoc}
   */
  public function forcast_employee($date, $asset) {
    $connection = \Drupal::database();
    $query = \Drupal::database()->select('users_field_data', 'u');
    $query->fields('u', ['uid', 'name']);
    $query->condition('u.uid', '1', '!=');
    $query->join('user__field_asset', 'fa', 'fa.entity_id = u.uid');
    $query->condition('fa.field_asset_target_id', $asset, '=');
    $query->fields('fa', ['entity_id']);
    $query->join('user__field_training_gap_score', 'ts', 'ts.entity_id = u.uid');
    $query->fields('ts', ['field_training_gap_score_value']);
    $query->join('user__field_employee_status', 'st', 'st.entity_id = u.uid');
    $query->condition('st.field_employee_status_value', 1, '=');
    $query->fields('st', ['field_employee_status_value']);
    $result = $query->execute()->fetchAll();
    $total_compliance = 0;
    $asset_compliance = 0;
    $rows = [];
    foreach ($result as $key => $value) {
      $status = 0;
      $name = $value->name;
      $uid = $value->uid;
      $asset_comp = new AssetCompliance();
      $certi_results = $asset_comp->complianceScoreCalculation($uid, $date);
      $field_training_gap_score_value = $certi_results['training_gap_score'];
      $total_emp = $key + 1;
      $total_compliance = $total_compliance + $field_training_gap_score_value;
      $asset_compliance = number_format(($total_compliance / ($total_emp * 100)) * 100);
    }
    return (int) $asset_compliance;
  }

  /**
   * Assets.
   */
  public function get_asset_by_customer() {
    $customer_id = $_POST['customer_id'];
    if ($customer_id != '_none') {
      $assets = getAssetsOptions($customer_id);
    }
    else {
      $assets = getAssetsOptions();
    }
    $html = '';
    foreach ($assets as $key => $value) {
      $html .= '<option value="' . $key . '">' . $value . '</option>';
    }
    echo $html;die;
  }

  /**
   * Jobtitle.
   */
  public function get_jobtitle_by_asset() {
    $html = '';
    if (isset($_POST['asset_id'])) {
      $asset_id = $_POST['asset_id'];
      if ($asset_id != '_none') {
        $jobtitle = getSelect2Options($asset_id);
      }
      else {
        $jobtitle = getSelect2Options();
      }
      foreach ($jobtitle as $key => $value) {
        $html .= '<option value="' . $key . '">' . $value . '</option>';
      }
    }
    echo $html;die;
  }

}
