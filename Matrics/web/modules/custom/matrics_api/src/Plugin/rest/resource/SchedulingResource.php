<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a Scheduling Resource.
 *
 * @RestResource(
 *   id = "scheduling",
 *   label = @Translation("Scheduling"),
 *   uri_paths = {
 *     "create" = "/matrics_api/scheduling"
 *   }
 * )
 */
class SchedulingResource extends ResourceBase {

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
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $response = ['message' => $this->t('Please Post asset key.')];
    $code = 400;
    if (!empty($data['asset_id'])) {
      if (empty($data['date'])) {
        $query = \Drupal::database()->select('user__field_asset', 'n')
          ->fields('n', ['entity_id'])
          ->condition('n.field_asset_target_id', $data['asset_id']);
        $query->join('users_field_data', 'u', 'u.uid = n.entity_id');
        $query->fields('u', ['name', 'uid']);
        $query->join('user__field_employee_status', 'us', 'us.entity_id = n.entity_id');
        $query->condition('us.field_employee_status_value', '1');
        $user = $query->execute()->fetchAll();

        foreach ($user as $uid) {
          $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
            ->fields('jt', ['field_job_title_target_id'])
            ->condition('jt.entity_id', $uid->entity_id);
          $job_title = $query2->execute()->fetchAll();

          $score = \Drupal::database()->select('user__field_training_gap_score', 'jt')
            ->fields('jt', ['field_training_gap_score_value'])
            ->condition('jt.entity_id', $uid->entity_id);
          $gap_score = $score->execute()->fetchAll();
          $job_title_name = get_job_title_name($job_title[0]->field_job_title_target_id);
          $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
          $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
          $query3->condition('fc.entity_id', $job_title[0]->field_job_title_target_id);
          $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
          $query3->fields('n', ['title']);
          $certi = $query3->execute()->fetchAll();

          $certificate_list = list_certificate($uid->entity_id);
          $user_certificate = certificate_expiration_date($uid->entity_id);

          $row[$uid->entity_id][0] = '<a href="/user/' . $uid->entity_id . '" target="_blank"> ' . $uid->name . '</a>';
          $row[$uid->entity_id][1] = $job_title_name;
          $row[$uid->entity_id][2] = "<a href='/job_scheduling/" . $job_title[0]->field_job_title_target_id . "/" . $uid->entity_id . "/" . $data['asset_id'] . "' class='use-ajax reserve'  data-dialog-options='{&quot;width&quot;:800}' data-dialog-type='modal' >Reserves</a>";
          $row_key = 3;
          $row_key2 = 0;
          $color = '';
          foreach ($certi as $certi_name) {
            if (!empty($certi_name->title)) {
              if (in_array($certi_name->title, $certificate_list)) {
                $color = '';
                $field_expiry_date = strtotime($user_certificate[$certi_name->title]);
                $current_date = new DrupalDateTime();
                $current_date = strtotime($current_date);

                if ($field_expiry_date > $current_date) {
                  $diff = abs($field_expiry_date - $current_date);
                  $years = floor($diff / (365 * 60 * 60 * 24));
                  $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

                  if ($months >= 6 || $years != 0) {
                    $color = '#C6EFCE';
                  }
                  elseif ($months <= 6 && $months >= 3) {
                    $color = '#FFCE33';
                  }
                  elseif ($months <= 3 && $months >= 1) {
                    $color = '#FFEB9C';
                  }
                  else {
                    $color = '#FFC7CE';
                  }
                  $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title . "(" . $user_certificate[$certi_name->title] . ")";
                  $row[$uid->entity_id][$row_key][$row_key2]['color'] .= $color;
                }
                else {
                  $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title . "(" . $user_certificate[$certi_name->title] . ")";
                  $row[$uid->entity_id][$row_key][$row_key2]['color'] .= '#FFC7CE';
                }
                $row_key2++;
              }
              else {
                $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title;
                $row[$uid->entity_id][$row_key][$row_key2]['color'] .= '#FFC7CE';
                $row_key2++;
              }
            }
          }
          $row[$uid->entity_id]['score'] = $gap_score[0]->field_training_gap_score_value . "%";
          $output = $row;
        }
        $response = [$output];
        $code = 200;
      }
      else {
        $query = \Drupal::database()->select('user__field_asset', 'n')
          ->fields('n', ['entity_id'])
          ->condition('n.field_asset_target_id', $data['asset_id']);
        $query->join('users_field_data', 'u', 'u.uid = n.entity_id');
        $query->fields('u', ['name', 'uid']);
        $query->join('user__field_employee_status', 'us', 'us.entity_id = n.entity_id');
        $query->condition('us.field_employee_status_value', '1');
        $user = $query->execute()->fetchAll();

        foreach ($user as $uid) {
          $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
            ->fields('jt', ['field_job_title_target_id'])
            ->condition('jt.entity_id', $uid->entity_id);
          $job_title = $query2->execute()->fetchAll();

          $score = \Drupal::database()->select('user__field_training_gap_score', 'jt')
            ->fields('jt', ['field_training_gap_score_value'])
            ->condition('jt.entity_id', $uid->entity_id);
          $gap_score = $score->execute()->fetchAll();

          $job_title_name = get_job_title_name($job_title[0]->field_job_title_target_id);

          $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
          $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
          $query3->condition('fc.entity_id', $job_title[0]->field_job_title_target_id);
          $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
          $query3->fields('n', ['title']);
          $certi = $query3->execute()->fetchAll();
          $certificate_list = list_certificate($uid->entity_id);
          $user_certificate = certificate_expiration_date($uid->entity_id);

          $row[$uid->entity_id][0] = '<a href="/user/' . $uid->entity_id . '" target="_blank"> ' . $uid->name . '</a>';
          $row[$uid->entity_id][1] = $job_title_name;
          $row[$uid->entity_id][2] = "<a href='/job_scheduling/" . $job_title[0]->field_job_title_target_id . "/" . $uid->entity_id . "/" . $data['asset_id'] . "' class='use-ajax'  data-dialog-options='{&quot;width&quot;:800}' data-dialog-type='modal'>Reserves</a>";
          $row_key = 3;
          $row_key2 = 0;
          $color = '';
          $total_certificate = 0;
          $valid_certificate = 0;
          foreach ($certi as $certi_name) {
            $total_certificate += 1;
            if (!empty($certi_name->title)) {
              if (in_array($certi_name->title, $certificate_list)) {
                $color = '';

                $field_expiry_date = strtotime($user_certificate[$certi_name->title]);
                $current_date = $data['date'];
                $current_date = strtotime($current_date);

                if ($field_expiry_date > $current_date) {
                  $diff = abs($field_expiry_date - $current_date);
                  $years = floor($diff / (365 * 60 * 60 * 24));
                  $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

                  if ($months >= 6 || $years != 0) {
                    $color = '#C6EFCE';
                  }
                  elseif ($months <= 6 && $months >= 3) {
                    $color = '#FFCE33';
                  }
                  elseif ($months <= 3 && $months >= 1) {
                    $color = '#FFEB9C';
                  }
                  else {
                    $color = '#FFC7CE';
                  }
                  $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title . "(" . $user_certificate[$certi_name->title] . ")";
                  $row[$uid->entity_id][$row_key][$row_key2]['color'] .= $color;
                  $valid_certificate += 1;
                }
                else {
                  $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title . "(" . $user_certificate[$certi_name->title] . ")";
                  $row[$uid->entity_id][$row_key][$row_key2]['color'] .= '#FFC7CE';
                }
                $row_key2++;
              }
              else {
                $row[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title;
                $row[$uid->entity_id][$row_key][$row_key2]['color'] .= '#FFC7CE';
                $row_key2++;
              }
            }
          }
          $training_gap_score = number_format(($valid_certificate / ($total_certificate)) * 100);
          $row[$uid->entity_id]['score'] = $training_gap_score . "%";
          $output = $row;
        }

        // current.
        foreach ($user as $uid) {
          $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
            ->fields('jt', ['field_job_title_target_id'])
            ->condition('jt.entity_id', $uid->entity_id);
          $job_title = $query2->execute()->fetchAll();

          $score = \Drupal::database()->select('user__field_training_gap_score', 'jt')
            ->fields('jt', ['field_training_gap_score_value'])
            ->condition('jt.entity_id', $uid->entity_id);
          $gap_score = $score->execute()->fetchAll();
          $job_title_name = get_job_title_name($job_title[0]->field_job_title_target_id);
          $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
          $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
          $query3->condition('fc.entity_id', $job_title[0]->field_job_title_target_id);
          $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
          $query3->fields('n', ['title']);
          $certi = $query3->execute()->fetchAll();

          $certificate_list = list_certificate($uid->entity_id);
          $user_certificate = certificate_expiration_date($uid->entity_id);

          $row1[$uid->entity_id][0] = '<a href="/user/' . $uid->entity_id . '" target="_blank"> ' . $uid->name . '</a>';
          $row1[$uid->entity_id][1] = $job_title_name;
          $row1[$uid->entity_id][2] = "<a href='/job_scheduling/" . $job_title[0]->field_job_title_target_id . "/" . $uid->entity_id . "/" . $data['asset_id'] . "' class='use-ajax reserve'  data-dialog-options='{&quot;width&quot;:800}' data-dialog-type='modal' >Reserves</a>";
          $row_key = 3;
          $row_key2 = 0;
          $color = '';

          foreach ($certi as $certi_name) {
            if (!empty($certi_name->title)) {
              if (in_array($certi_name->title, $certificate_list)) {
                $color = '';
                $field_expiry_date = strtotime($user_certificate[$certi_name->title]);
                $current_date = new DrupalDateTime();
                $current_date = strtotime($current_date);

                if ($field_expiry_date > $current_date) {
                  $diff = abs($field_expiry_date - $current_date);
                  $years = floor($diff / (365 * 60 * 60 * 24));
                  $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

                  if ($months >= 6 || $years != 0) {
                    $color = '#C6EFCE';
                  }
                  elseif ($months <= 6 && $months >= 3) {
                    $color = '#FFCE33';
                  }
                  elseif ($months <= 3 && $months >= 1) {
                    $color = '#FFEB9C';
                  }
                  else {
                    $color = '#FFC7CE';
                  }
                  $row1[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title . "(" . $user_certificate[$certi_name->title] . ")";
                  $row1[$uid->entity_id][$row_key][$row_key2]['color'] .= $color;
                }
                else {
                  $row1[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title . "(" . $user_certificate[$certi_name->title] . ")";
                  $row1[$uid->entity_id][$row_key][$row_key2]['color'] .= '#FFC7CE';
                }
                $row_key2++;
              }
              else {
                $row1[$uid->entity_id][$row_key][$row_key2]['date'] .= $certi_name->title;
                $row1[$uid->entity_id][$row_key][$row_key2]['color'] .= '#FFC7CE';
                $row_key2++;
              }
            }
          }
          $row1[$uid->entity_id]['score'] = $gap_score[0]->field_training_gap_score_value . "%";
          $output2 = $row1;
        }
        $response = [$output, $output2];
        $code = 200;
      }
    }
    return new ResourceResponse($response, $code);
  }

}
