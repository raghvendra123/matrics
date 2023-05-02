<?php

namespace Drupal\training_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\charts\Services\ChartsSettingsServiceInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * An TrainingManagement controller.
 */
class TrainingManagement extends ControllerBase {
  /**
   * The charts settings.
   *
   * @var \Drupal\charts\Services\ChartsSettingsServiceInterface
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
   *   The chartsetting service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messanger service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuidService
   *   The uuid service.
   */
  public function __construct(ChartsSettingsServiceInterface $chartSettings, MessengerInterface $messenger, UuidInterface $uuidService) {
    $this->chartSettings = $chartSettings->getChartsSettings();
    $this->messenger = $messenger;
    $this->uuidService = $uuidService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('charts.settings'),
      $container->get('messenger'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function manage($job = '', $user = '', $asset = '') {
    $nids = \Drupal::entityQuery('node')->condition('type', 'asset')->execute();
    $nodes = Node::loadMultiple($nids);
    $six_plus = 0;
    $six_to_three = 0;
    $three_to_one = 0;
    $one_month_or_expired = 0;

    foreach ($nodes as $key => $value) {
      $asset_id = $value->nid->value;
      $query1 = \Drupal::database()->select('node__field_asset', 'fa')
        ->fields('fa', ['entity_id'])
        ->condition('fa.field_asset_target_id', $asset_id)
        ->condition('fa.bundle', 'job_title');
      $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
      $query1->fields('n', ['title']);
      $job_title = $query1->execute()->fetchAll();
      foreach ($job_title as $job) {
        $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
          ->fields('jt', ['entity_id'])
          ->condition('jt.field_job_title_target_id', $job->entity_id);
        $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
        $query2->fields('u', ['name', 'uid']);
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
          $certificate_name[get_job_title_name($cer->field_certificate_target_id)] .= get_job_title_name($cer->field_certificate_target_id);
        }

        foreach ($job_user as $user) {
          $row = [];
          $certificate_list = '';
          $certificate_list = list_certificate($user->uid);
          $user_certificate = certificate_expiration_date($user->uid);
          $row[0] = $user->name;
          // dump($user_certificate); die;.
          $row_key = 1;
          foreach ($certificate_name as $certi) {
            if (!empty($certi)) {
              if (in_array($certi, $certificate_list)) {
                $color = '';
                $field_expiry_date = strtotime($user_certificate[$certi]);
                $current_date = new DrupalDateTime();
                $current_date = strtotime($current_date);
                if ($field_expiry_date > $current_date) {
                  $diff = abs($field_expiry_date - $current_date);
                  $years = floor($diff / (365 * 60 * 60 * 24));
                  $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                  if ($months >= 6 || $years != 0) {
                    $six_plus++;
                    $row[$row_key]['six_plus'] .= $six_plus;
                  }
                  elseif ($months <= 6 && $months >= 3) {
                    $six_to_three++;
                    $row[$row_key]['six_to_three'] = $six_to_three;
                  }
                  elseif ($months <= 3 && $months >= 1) {
                    $three_to_one++;
                    $row[$row_key]['three_to_one'] = $three_to_one;
                  }
                  else {
                    $one_month_or_expired = $one_month_or_expired++;
                  }
                  $row[$row_key]['date'] = $user_certificate[$certi];
                }
                else {
                  $row[$row_key]['date'] = $user_certificate[$certi];
                  $one_month_or_expired++;
                  $row[$row_key]['one_month_or_expired'] = $one_month_or_expired;
                }
                $row_key++;
              }
              else {
                $row[$row_key]['date'] .= '0';
                $one_month_or_expired++;
                $row[$row_key]['one_month_or_expired'] = $one_month_or_expired;
                $row_key++;
              }
            }
          }
        }
      }
    }
    $library = $this->chartSettings['library'];
    if (empty($library)) {
      $this->messenger->addError($this->t('You need to first configure Charts default settings'));
      return [];
    }
    // Customize options here.
    $options = [
      'type' => $this->chartSettings['type'],
      'title' => $this->t('Training Management'),
      'xaxis_title' => $this->t('X-Axis'),
      'yaxis_title' => $this->t('Y-Axis'),
      'yaxis_min' => '',
      'yaxis_max' => '',
      'three_dimensional' => FALSE,
      'title_position' => 'out',
      'legend_position' => 'right',
      'data_labels' => $this->chartSettings['data_labels'],
      'tooltips' => $this->chartSettings['tooltips'],
      'colors'   => $this->chartSettings['colors'],
      'min' => $this->chartSettings['min'],
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
    $seriesData[] = [
      'name' => '1 Month or Expired',
      'color' => '#FFC7CE',
      'type' => 'column',
      'data' => [40, 98, 106, 205],
    ];
    switch ($this->chartSettings['type']) {
      default:
        $seriesData[] = [
          'name' => '3 Months and 1 Month',
          'color' => '#FFEB9C',
          'type' => 'column',
          'data' => [90, 145, 60, 90],
        ];

        $seriesData[] = [
          'name' => '6 Months and 3 Months',
          'color' => '#FFCE33',
          'type' => 'column',
          'data' => [150, 450, 500, 300],
        ];

        $seriesData[] = [
          'name' => 'More than 6 Months',
          'color' => '#C6EFCE',
          'type' => $this->chartSettings['type'],
          'data' => [250, 350, 400, 200],
        ];
      case 'pie':
      case 'donut':
    }

    // Creates a UUID for the chart ID.
    $chartId = 'chart-' . $this->uuidService->generate();

    $build = [
      '#theme' => 'charts_api_example',
      '#library' => (string) $library,
      '#categories' => $categories,
      '#seriesData' => $seriesData,
      '#options' => $options,
      '#id' => $chartId,
      '#override' => [],
    ];

    $output['status']['#FFC7CE'] = $one_month_or_expired;
    $output['status']['#FFEB9C'] = $three_to_one;
    $output['status']['#FFCE33'] = $six_to_three;
    $output['status']['#C6EFCE'] = $six_plus;
    $output['chart'] = render($build);

    $element = [
      '#theme' => 'training_management',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
    ];

    return $element;
  }

}
