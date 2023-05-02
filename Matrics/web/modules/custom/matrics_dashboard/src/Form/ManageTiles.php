<?php

namespace Drupal\matrics_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a form for draggable table testing.
 */
class ManageTiles extends FormBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a TableDragTestForm object.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('state'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tabledrag_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['save1'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
    $form['toggle_status'] = [
      '#type' => 'checkbox',
      '#attributes' => ['class' => ['toggle_status check-box']],
      '#title' => 'Toggle All Status',
      '#prefix' => '<div class="toggle"><span>Toggle All Tiles</span>',
      '#suffix' => '</div>',
    ];
    $form['toggle_chart'] = [
      '#type' => 'checkbox',
      '#attributes' => ['class' => ['toggle_chart check-box']],
      '#title' => 'Toggle All Chart Status',
      '#prefix' => '<div class="toggle"><span>Toggle All Chart</span>',
      '#suffix' => '</div>',
    ];
    $form['table'] = [
      '#type' => 'table',
      '#header' => [
        [
          'data' => $this->t('Tiles'),
          'colspan' => 4,
        ],
        $this->t('Weight'),
        $this->t('Status'),
        $this->t('Chart status'),
        // $this->t('Operations'),.
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'tabledrag-test-weight',
        ],
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'tabledrag-test-parent',
          'subgroup' => 'tabledrag-test-parent',
          'source' => 'tabledrag-test-id',
          'hidden' => TRUE,
          'limit' => 2,
        ],
        [
          'action' => 'depth',
          'relationship' => 'group',
          'group' => 'tabledrag-test-depth',
          'hidden' => TRUE,
        ],
      ],
      '#attributes' => ['id' => 'tabledrag-test-table'],
      // '#attached' => ['library' => ['tabledrag_test/tabledrag']],
    ];

    // Provide a default set of five rows.
    $database = \Drupal::database();
    $query = $database->select('dashboard_manage', 'dm');
    $query->fields('dm');
    $result = $query->execute();
    $result = $result->fetchAll();

    $data = [];
    foreach ($result as $id => $row1) {
      $data[$row1->tile_order] = [
        'tid' => $row1->id,
        'uid' => $row1->uid,
        'tiles' => $row1->tiles,
        'settings' => $row1->settings,
        'tile_order' => $row1->tile_order,
        'status' => $row1->status,
        'chart_status' => $row1->chart_status,
      ];
    }

    $data_set = $this->state->get('tiles_drag_val', array_flip(range(1, 19)));

    if (is_array($data_set[1])) {
      $data = $this->state->get('tiles_drag_val', array_flip(range(1, 19)));
    }

    foreach ($data as $id => $row1) {
      if (!is_array($row1)) {
        $row1 = [];
      }

      $row1 += [
        'tid' => $row1['tid'],
        'parent' => '',
        'weight' => isset($row1['tile_order']) ? $row1['tile_order'] : '',
        'depth' => 0,
        'status' => $row1['status'],
        'chart_status' => $row1['chart_status'],
        'classes' => [],
        'draggable' => TRUE,
      ];

      $tiles = $row1['tiles'];

      if (!empty($row1['draggable'])) {
        $row1['classes'][] = 'draggable';
      }

      $form['table'][$id] = [
        'title' => [
          'indentation' => [
            '#theme' => 'indentation',
            '#size' => $row1['depth'],
          ],
          '#plain_text' => $tiles,
        ],
        'id' => [
          '#type' => 'hidden',
          '#value' => $id,
          '#attributes' => ['class' => ['tabledrag-test-id']],
        ],
        'parent' => [
          '#type' => 'hidden',
          '#default_value' => $row1['parent'],
          '#parents' => ['table', $id, 'parent'],
          '#attributes' => ['class' => ['tabledrag-test-parent']],
        ],
        'depth' => [
          '#type' => 'hidden',
          '#default_value' => $row1['depth'],
          '#attributes' => ['class' => ['tabledrag-test-depth']],
        ],
        'weight' => [
          '#type' => 'weight',
          '#default_value' => isset($row1['weight']) ? $row1['weight'] : $row1['tile_order'],
          '#attributes' => ['class' => ['tabledrag-test-weight']],
        ],
        'tiles' => [
          '#type' => 'hidden',
          '#value' => $tiles,
        ],
        'status' => [
          '#type' => 'checkbox',
          '#default_value' => $row1['status'],
          '#attributes' => ['class' => ['status tabledrag-test-status check-box']],
          '#title' => 'Status',
        ],
        'chart_status' => [
          '#type' => 'checkbox',
          '#default_value' => $row1['chart_status'],
          '#attributes' => ['class' => ['chart_status tabledrag-test-chart-status check-box']],
          '#title' => 'Chart Status',
        ],
        'tid' => [
          '#type' => 'hidden',
          '#value' => $row1['tid'],
        ],

        '#attributes' => ['class' => $row1['classes']],
      ];
    }

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $test_table = [];
    foreach ($form_state->getValue('table') as $row) {
      $test_table[$row['id']] = $row;
      $num_updated = \Drupal::database()->update('dashboard_manage')
        ->fields([
          'tile_order' => $row['weight'],
          'status' => $row['status'],
          'chart_status' => $row['chart_status'],
        ])
        ->condition('id', $row['tid'], '=')
        ->execute();
    }
    $this->state->set('tiles_drag_val', $test_table);
    \Drupal::messenger()->addMessage('Updated records successfully');
    $response = new RedirectResponse('/');
    $response->send();
  }

}
