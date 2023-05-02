<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a Training Chart Resource.
 *
 * @RestResource(
 *   id = "training_chart",
 *   label = @Translation("Training Chart"),
 *   uri_paths = {
 *     "canonical" = "/training_chart",
 *     "create" = "/training_chart"
 *   }
 * )
 */
class TrainingChartResource extends ResourceBase {

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
    $connection = \Drupal::database();
    $query = $connection->select('user__field_certificates ', 'c');
    $query->fields('c', ['entity_id', 'field_certificates_target_id']);
    $query->join('user__roles', 'r', 'c.entity_id = r.entity_id');
    $query->condition('r.roles_target_id', 'individual_employee');
    $query->join('paragraph__field_expiry_date', 'd', 'd.entity_id = c.field_certificates_target_id');
    $query->join('paragraph__field_certificate_type', 't', 't.entity_id = d.entity_id');
    $query->join('user__field_job_title', 'j', 'j.entity_id = c.entity_id');
    $query->fields('d', ['field_expiry_date_value']);
    $user_certificate = $query->execute()->fetchAll();

    $six_plus = 0;
    $six_plus1 = 0;
    $six_plus2 = 0;
    $six_to_three = 0;
    $six_to_three1 = 0;
    $six_to_three2 = 0;
    $three_to_one = 0;
    $three_to_one1 = 0;
    $three_to_one2 = 0;
    $one_month = 0;
    $one_month1 = 0;
    $one_month2 = 0;
    $expired = 0;
    $expired1 = 0;
    $expired2 = 0;

    foreach ($user_certificate as $certificate) {
      $field_expiry_date = strtotime($certificate->field_expiry_date_value);
      $current_date = new DrupalDateTime();
      $current_date = strtotime($current_date);

      if ($field_expiry_date > $current_date) {
        $diff = abs($field_expiry_date - $current_date);
        $years = round($diff / 60 / 60 / 24 / 365);
        $months = round($diff / 60 / 60 / 24 / 30);

        if ($months <= 6 && $months > 3) {
          $six_to_three++;
        }
        elseif ($months <= 3 && $months > 1) {
          $three_to_one++;
        }
        else {
          $one_month++;
        }
      }
      else {
        $expired++;
      }

      $field_expiry_date1 = strtotime($certificate->field_expiry_date_value);
      $current_date1 = new DrupalDateTime('+1 month');
      $current_date1 = strtotime($current_date1);

      if ($field_expiry_date1 > $current_date1) {
        $diff1 = abs($field_expiry_date1 - $current_date1);
        $years1 = round($diff1 / 60 / 60 / 24 / 365);
        $months1 = round($diff1 / 60 / 60 / 24 / 30);

        if ($months1 <= 6 && $months1 > 3) {
          $six_to_three1++;
        }
        elseif ($months1 <= 3 && $months1 > 1) {
          $three_to_one1++;
        }
        else {
          $one_month1++;
        }
      }
      else {
        $expired1++;
      }

      $field_expiry_date2 = strtotime($certificate->field_expiry_date_value);
      $current_date2 = new DrupalDateTime('+1 month');
      $current_date2 = strtotime($current_date2);

      if ($field_expiry_date2 > $current_date2) {
        $diff2 = abs($field_expiry_date2 - $current_date2);
        $years2 = round($diff2 / 60 / 60 / 24 / 365);
        $months2 = round($diff2 / 60 / 60 / 24 / 30);

        if ($months2 <= 6 && $months2 > 3) {
          $six_to_three2++;
        }
        elseif ($months2 <= 3 && $months2 > 1) {
          $three_to_one2++;
        }
        else {
          $one_month2++;
        }
      }
      else {
        $expired2++;
      }

      // $data['six_plus'][0] = $six_plus;
      $data['six_to_three'][0] = $six_to_three;
      $data['three_to_one'][0] = $three_to_one;
      $data['one_month'][0] = $one_month;
      $data['expired'][0] = $expired;

      // $data['six_plus'][1] = $six_plus1;
      $data['six_to_three'][1] = $six_to_three1;
      $data['three_to_one'][1] = $three_to_one1;
      $data['one_month'][1] = $one_month1;
      $data['expired'][1] = $expired1;

      // $data['six_plus'][2] = $six_plus2;
      $data['six_to_three'][2] = $six_to_three2;
      $data['three_to_one'][2] = $three_to_one2;
      $data['one_month'][2] = $one_month2;
      $data['expired'][2] = $expired2;
    }
    $months = [];
    $currentMonth = (int) date('m');
    for ($x = $currentMonth; $x < $currentMonth + 3; $x++) {
      $months[] = date('M', mktime(0, 0, 0, $x, 1));
    }

    $categories = $months;
    $response = ['seriesData' => $data, 'categories' => $categories];

    return new ResourceResponse($response);
  }

}
