<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Demo Resource.
 *
 * @RestResource(
 *   id = "reporting_preview",
 *   label = @Translation("Reporting Preview"),
 *   uri_paths = {
 *     "create" = "/matrics_api/reporting_preview"
 *   }
 * )
 */
class ReportingPreview extends ResourceBase {

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
   * Responds to POST requests with asset.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $response = ['message' => $this->t('Please Post title key.')];
    $code = 400;
    if (!empty($data)) {
      $header = [
        'Employee Name',
        'Certificate link',
        'Certificate type',
        'Certificate start date',
        'Certificate expiry date',
      ];
      // Find and load all of the Article nodes we are going to include
      // $connection = \Drupal::database();
      $query = \Drupal::database()->select('users_field_data', 'u');
      $query->fields('u', ['uid', 'name']);
      $query->condition('u.uid', '1', '!=');
      $result = $query->execute()->fetchAll();
      foreach ($result as $key => $node) {
        $certificate = get_certificate($node->uid);
        $data1[$key][] = $node->name;
        $data1[$key][] = $certificate['link'];
        $data1[$key][] = $certificate['type'];
        $data1[$key][] = $certificate['start_date'];
        $data1[$key][] = $certificate['expiry_date'];
      }
      $response = [$data1];
      $code = 200;
    }
    return new ModifiedResourceResponse($response, $code);
  }

}
