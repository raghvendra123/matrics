<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\node\Entity\Node;
use Drupal\matrics_reports\Controller\AssetCompliance;
use Drupal\matrics_reports\Controller\AssetList;

/**
 * Provides a Asset Compliance Resource.
 *
 * @RestResource(
 *   id = "asset_compliance",
 *   label = @Translation("Asset Compliance"),
 *   uri_paths = {
 *     "canonical" = "/asset_compliance",
 *     "create" = "/asset_compliance"
 *   }
 * )
 */
class AssetComplianceResource extends ResourceBase {

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
    // $response = ['message' => $this->t('Please Post asset key.')];
    // $code = 400;
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $output = $this->getassetlist();

    $response = ['data' => $output];
    $code = 200;
    return new ResourceResponse($response, $code);
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
    $response = ['message' => $this->t('Please Post Asset, Compliance Score Min and Compliance Score Max key.')];
    $code = 400;
    if (!empty($data)) {
      $output = $this->getassetlist($data);
      $response = ['data' => $output];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

  /**
   * {@inheritdoc}
   */
  public function getassetlist($fields = []) {
    $compliance_score_query = FALSE;
    $compliance_score_min = $compliance_score_max = NULL;
    $first_load = FALSE;
    if (!empty($fields)) {
      $assets = $fields['asset_id'];
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
        $nids = \Drupal::entityQuery('node')->condition('type', 'asset')->execute();
        $nodes = Node::loadMultiple($nids);
      }
    }
    else {
      $nids = \Drupal::entityQuery('node')->condition('type', 'asset')->execute();
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
        // $field_training_gap_score_value = $value1->field_training_gap_score_value;
        $total_emp = $key + 1;
        $total_compliance = $total_compliance + $field_training_gap_score_value;
        $asset_compliance = number_format(($total_compliance / ($total_emp * 100)) * 100);
      }

      for ($x = $currentMonth; $x < $currentMonth + 12; $x++) {
        $months[] = date('M', mktime(0, 0, 0, $x, 1));
        $date = date('m' . "/" . date('d') . "/" . 'y', mktime(0, 0, 0, $x, 1));
        $per[] = AssetList::forcast_employee($date, $nid);
      }
      // Sample data format.
      $categories = $months;
      $seriesData[] = [
        'name' => 'Asset Score',
        'color' => '#0d233a',
        'type' => 'line',
        'data' => $per,
      ];

      $chart = [
        'categories' => $categories,
        'seriesData' => $seriesData,
        'options' => $options,
      ];
      $asset_compliance = (int) $asset_compliance;

      if (!empty($compliance_score_min) && ($compliance_score_min < $asset_compliance && $compliance_score_max > $asset_compliance)) {
        $data[] = [
          'title' => $title,
          'url' => '/asset-compliance/' . $nid,
          'complience_score' => $asset_compliance,
          'chart' => $chart,
        ];
      }
      elseif (!empty($compliance_score_query) && $compliance_score_min == 0 && ($compliance_score_min < $asset_compliance && $compliance_score_max > $asset_compliance)) {
        $data[] = [
          'title' => $title,
          'url' => '/asset-compliance/' . $nid,
          'complience_score' => $asset_compliance,
          'chart' => $chart,
        ];
      }
      elseif ($compliance_score_min == 0 && empty($compliance_score_query) && ($compliance_score_max > $asset_compliance)) {
        $data[] = [
          'title' => $title,
          'url' => '/asset-compliance/' . $nid,
          'complience_score' => $asset_compliance,
          'chart' => $chart,
        ];
      }
      elseif ($first_load) {
        $data[] = [
          'title' => $title,
          'url' => '/asset-compliance/' . $nid,
          'complience_score' => $asset_compliance,
          'chart' => $chart,
        ];
      }
      else {
      }
    }
    return $data;
  }

}
