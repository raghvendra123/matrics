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
 * Provides a Type Resource.
 *
 * @RestResource(
 *   id = "types",
 *   label = @Translation("Type List"),
 *   uri_paths = {
 *     "canonical" = "/types"
 *   }
 * )
 */
class TypeResource extends ResourceBase {

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
    $query->join('paragraph__field_enable', 'e', 'e.entity_id = c.field_certificates_target_id');
    $query->fields('e', ['field_enable_value']);
    $query->condition('e.field_enable_value', 1);
    $query->join('paragraph__field_course_name', 'cn', 'cn.entity_id = c.field_certificates_target_id');
    $query->fields('cn', ['field_course_name_target_id']);
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
      $current_date = new DrupalDateTime();
      $current_date = strtotime($current_date);
      if ($field_expiry_date < $current_date) {
        $expired++;
        $total_cert_count++;
        $query2 = \Drupal::database()->select('user__field_course_details', 'n')
          ->fields('n', ['field_course_details_target_id'])
          ->condition('n.entity_id', $certificate->entity_id);
        $query2->join('paragraph__field_course_', 'c', 'c.entity_id = n.field_course_details_target_id');
        $query2->condition('c.field_course__target_id', $certificate->field_course_name_target_id);
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
        $months = round($diff / 60 / 60 / 24 / 30);
        if ($months > 6 || $years != 0) {
          $six_plus++;
        }
        elseif ($months <= 6 && $months > 3) {
          $six_to_three++;
          $total_cert_count++;

          $query2 = \Drupal::database()->select('user__field_course_details', 'n')
            ->fields('n', ['field_course_details_target_id'])
            ->condition('n.entity_id', $certificate->entity_id);
          $query2->join('paragraph__field_course_', 'c', 'c.entity_id = n.field_course_details_target_id');
          $query2->condition('c.field_course__target_id', $certificate->field_course_name_target_id);
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
          $query2->join('paragraph__field_course_', 'c', 'c.entity_id = n.field_course_details_target_id');
          $query2->condition('c.field_course__target_id', $certificate->field_course_name_target_id);
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
          $query2->join('paragraph__field_course_', 'c', 'c.entity_id = n.field_course_details_target_id');
          $query2->condition('c.field_course__target_id', $certificate->field_course_name_target_id);
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
    $output['status']['expired'] = $expired;
    $output['status']['one_month'] = $one_month;
    $output['status']['three_to_one'] = $three_to_one;
    $output['status']['six_to_three'] = $six_to_three;
    if (!empty($output['status'])) {
      $option = [
        'expired' => 'expired',
        'one_month' => 'Within 1 month',
        // 'six_to_one' => 'Within 6 to 1 month',
        'three_to_one' => 'Within 3 to 1 month',
        'six_to_three' => 'Within 6 to 3 month',
      ];
      $response['types'] = $option;
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
      if ($val != 0) {
        $percent = ($action * 100) / $val;
      }
      else {
        $percent = 0;
      }
      $data[$key] = [
        'class' => $class,
        'title' => $title,
        'val' => $val,
        'action' => $action,
        'to_be_action' => $to_be_action,
        'percent' => $percent,
        'total_cert_count' => $total_cert_count,
      ];
    }
    $response['processbar_counts'] = $data;
    $code = 200;
    return new ResourceResponse($response, $code);
  }

}
