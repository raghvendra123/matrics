<?php

namespace Drupal\training_management\Plugin\Block;

/**
 * @file
 * Contains \Drupal\training_management\Plugin\Block\TrainingLineChart.
 */

use Drupal\Core\Block\BlockBase;
use Drupal\charts\Services\ChartsSettingsServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Training Management Chart .
 *
 * @Block(
 *   id = "training_line_chart",
 *   admin_label = @Translation("Training line Chart"),
 * )
 */
class TrainingLineChart extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The charts settings.
   *
   * @var \Drupal\charts\Services\ChartsSettingsServiceInterface
   */
  protected $chartSettings;

  /**
   * Create.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container service.
   * @param array $configuration
   *   The configuration service.
   * @param string $plugin_id
   *   The plugin_id service.
   * @param mixed $plugin_definition
   *   The plugin_definition service.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('charts.settings')
    );
  }

  /**
   * Construct.
   *
   * @param array $configuration
   *   The configuration service.
   * @param string $plugin_id
   *   The plugin_id service.
   * @param mixed $plugin_definition
   *   The plugin_definition service.
   * @param array $chartSettings
   *   The chartSettings service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ChartsSettingsServiceInterface $chartSettings) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->chartSettings = $chartSettings->getChartsSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $connection = \Drupal::database();
    $query = $connection->select('user__field_certificates ', 'c');
    $query->fields('c', ['entity_id', 'field_certificates_target_id']);
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

        if ($months > 6 || $years != 0) {
          $six_plus++;
        }
        elseif ($months <= 6 && $months > 3) {
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

        if ($months1 > 6 || $years1 != 0) {
          $six_plus1++;
        }
        elseif ($months1 <= 6 && $months1 > 3) {
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

        if ($months2 > 6 || $years2 != 0) {
          $six_plus2++;
        }
        elseif ($months2 <= 6 && $months2 > 3) {
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

      $data['six_plus'][0] = $six_plus;
      $data['six_to_three'][0] = $six_to_three;
      $data['three_to_one'][0] = $three_to_one;
      $data['one_month'][0] = $one_month;
      $data['expired'][0] = $expired;

      $data['six_plus'][1] = $six_plus1;
      $data['six_to_three'][1] = $six_to_three1;
      $data['three_to_one'][1] = $three_to_one1;
      $data['one_month'][1] = $one_month1;
      $data['expired'][1] = $expired1;

      $data['six_plus'][2] = $six_plus2;
      $data['six_to_three'][2] = $six_to_three2;
      $data['three_to_one'][2] = $three_to_one2;
      $data['one_month'][2] = $one_month2;
      $data['expired'][2] = $expired2;
    }

    $library = $this->chartSettings['library'];

    // Customize options here.
    $options = [
      'type' => $this->chartSettings['type'],
      'xaxis_title' => $this->t('Month'),
      'yaxis_title' => $this->t('Certificates Count'),
      'yaxis_min' => '',
      'yaxis_max' => '',
      'three_dimensional' => FALSE,
      'title_position' => 'out',
      'legend_position' => 'right',
      'data_labels' => $this->chartSettings['data_labels'],
      'tooltips' => $this->chartSettings['tooltips'],
      // 'grouping'   => TRUE,
      'colors'   => $this->chartSettings['colors'],
      'min'   => $this->chartSettings['min'],
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
    $months = [];
    $currentMonth = (int) date('m');
    for ($x = $currentMonth; $x < $currentMonth + 3; $x++) {
      $months[] = date('M', mktime(0, 0, 0, $x, 1));
    }

    $categories = $months;
    $seriesData[] = [
      'name' => 'Expired',
      'color' => '#f57070',
      'type' => 'area',
      'data' => $data['expired'],
      'opacity' => 1.0,
    ];
    $seriesData[] = [
      'name' => '1 Month',
      'color' => '#FFC7CE',
      'type' => 'area',
      'data' => $data['one_month'],
      'opacity' => 1.0,
    ];
    $seriesData[] = [
      'name' => '3 Months to 1 Month',
      'color' => '#FFEB9C',
      'type' => 'area',
      'data' => $data['three_to_one'],
      'opacity' => 1.0,
    ];
    $seriesData[] = [
      'name' => '6 Months to 3 Months',
      'color' => '#FFCE33',
      'type' => 'area',
      'data' => $data['six_to_three'],
      'opacity' => 1.0,
    ];
    $seriesData[] = [
      'name' => 'More than 6 Months',
      'color' => '#C6EFCE',
      'type' => 'area',
      'data' => $data['six_plus'],
      'opacity' => 1.0,
    ];
    $uuid_service = \Drupal::service('uuid');
    $uuid = $uuid_service->generate();
    $chartId = 'chart-' . $uuid;
    $build = [
      '#theme' => 'charts_api_example',
      '#library' => (string) $library,
      '#categories' => $categories,
      '#seriesData' => $seriesData,
      '#options' => $options,
      '#id' => $chartId,
      '#override' => [],
    ];
    return $build;
  }

}
