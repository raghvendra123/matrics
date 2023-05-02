<?php

namespace Drupal\matrics_dashboard\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * {@inheritdoc}
 */
class EmployeeOverview extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function overview() {
    return [
      '#theme' => 'employee_overview',
      '#attached' => [
        'library' => [
          'matrics_dashboard/matrics_styles',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
  }

}
