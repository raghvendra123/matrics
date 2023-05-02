<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\views\Views;
use Drupal\Component\Serialization\Json;

/**
 * Provides a Employee Overviews Resource.
 *
 * @RestResource(
 *   id = "employeeoverview",
 *   label = @Translation("Employee Overviews"),
 *   uri_paths = {
 *     "canonical" = "/employeeoverview",
 *     "create" = "/employeeoverview"
 *   }
 * )
 */
class EmployeeOverviewResource extends ResourceBase {

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
    $view = Views::getView('employee_overview');
    $view->setDisplay('rest_export_1');
    $exposed_filters = [
      'field_asset_target_id' => 272,
      'field_location_country_code' => "GB",
      'field_job_title_value' => "roust",
    ];
    $view->setExposedInput($exposed_filters);
    $view->execute();
    $output = \Drupal::service('renderer')->render($view->render());
    $render = $view->render();
    $decode = Json::decode($output);
    $response = ['employees' => Json::encode($decode)];
    // print_r(var_dump($decode));die;
    $code = 200;
    return new ResourceResponse($response, $code);
  }

  /**
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $response = ['message' => $this->t('Please Post asset key.')];
    $code = 400;
    if (!empty($data)) {
      $view = Views::getView('employee_overview');
      $view->setDisplay('rest_export_1');
      $exposed_filters = [
        'field_asset_target_id' => $data['field_asset_target_id'],
        'field_location_country_code' => $data['field_location_country_code'],
        'field_job_title_value' => $data['field_job_title_value'],
      ];
      $view->setExposedInput($exposed_filters);
      $view->execute();
      // $output = \Drupal::service('renderer')->render($view->render());
      // print_r($output);die;
      // $render= $view->render();
      // $decode = Json::decode($output);
      $response = ['employees_overview' => 'Json::encode($decode)'];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

}
