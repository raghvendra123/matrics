<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\matrics_reports\Controller\AssetCompliance;

/**
 * Provides a Asset Compliance By Id Resource.
 *
 * @RestResource(
 *   id = "asset_compliance_by_id",
 *   label = @Translation("Asset Compliance By Id"),
 *   uri_paths = {
 *     "canonical" = "/asset_compliance_by_id/{asset_id}",
 *     "create" = "/asset_compliance_by_id/{asset_id}"
 *   }
 * )
 */
class AssetComplianceByIdResource extends ResourceBase {

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
  public function get($asset_id = NULL) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $response = ['message' => t('Please check url')];
    $code = 400;
    if ($asset_id != NULL) {
      $expiry_date = date('Y-m-d');
      $output = $this->getListById($asset_id, $expiry_date);
      $response = ['data' => $output];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

  /**
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data, $asset_id = NULL) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $response = ['message' => $this->t('Please Post Expiry Date.')];
    $code = 400;
    if (!empty($data)) {
      $expiry_date = $data['expiry_date'];
      $output = $this->getListById($asset_id, $expiry_date);
      $response = ['data' => $output];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

  /**
   * {@inheritdoc}
   */
  public function getListById($asset_id = NULL, $expiry_date = NULL) {
    $connection = \Drupal::database();
    $query = \Drupal::database()->select('users_field_data', 'u');
    $query->fields('u', ['uid', 'name']);
    $query->condition('u.uid', '1', '!=');
    $query->join('user__field_asset', 'fa', 'fa.entity_id = u.uid');
    $query->condition('fa.field_asset_target_id', $asset_id, '=');
    $query->fields('fa', ['entity_id']);
    $query->join('user__field_training_gap_score', 'ts', 'ts.entity_id = u.uid');
    $query->fields('ts', ['field_training_gap_score_value']);
    $query->join('user__field_employee_status', 'st', 'st.entity_id = u.uid');
    $query->condition('st.field_employee_status_value', 1, '=');
    $query->fields('st', ['field_employee_status_value']);
    $result = $query->execute()->fetchAll();
    $total_compliance = 0;
    $rows = [];
    foreach ($result as $key => $value) {
      $status = 0;
      $name = $value->name;
      $uid = $value->uid;
      $assetcompliance = new AssetCompliance();
      $certi_results = $assetcompliance->complianceScoreCalculation($uid, $expiry_date);
      $field_training_gap_score_value = $certi_results['training_gap_score'];
      // $field_training_gap_score_value = $value->field_training_gap_score_value;
      $total_emp = $key + 1;
      $total_compliance = $total_compliance + $field_training_gap_score_value;
      $asset_compliance = number_format(($total_compliance / ($total_emp * 100)) * 100);
      if (!empty($value->field_employee_status_value)) {
        $status = "<span class='emp_status'>" . $value->field_employee_status_value . "</span>";
      }
      else {
        $status = "<span class='emp_status'>" . $value->field_employee_status_value . "</span>";
      }
      $entity_type_id = 'user';
      $field_name = 'field_employee_status';
      $entity_fields_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_type_id);
      $field_definition = $entity_fields_definitions[$field_name];
      $field_allowed_options = options_allowed_values($field_definition);
      $status = $field_allowed_options[$value->field_employee_status_value];
      $rows[] = [
        'id' => '/user/' . $uid,
        'user' => $name,
        'score' => $field_training_gap_score_value,
        'status' => $value->field_employee_status_value,
      ];
    }

    $data = [
      'total_emp' => $total_emp,
      'asset_compliance' => $asset_compliance,
      'total_compliance' => $total_compliance,
      'table' => $rows,
    ];
    return $data;
  }

}
