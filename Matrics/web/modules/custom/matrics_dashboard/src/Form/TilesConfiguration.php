<?php

namespace Drupal\matrics_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a form for draggable table testing.
 */
class TilesConfiguration extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'title_confuguraion';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $conn = \Drupal::database();
    $record = [];
    if (isset($id)) {
      $query = $conn->select('dashboard_manage', 'm')
        ->condition('id', $id)
        ->fields('m');
      $record = $query->execute()->fetchAssoc();
    }

    $form['enable'] = [
      '#type' => 'checkbox',
      '#title' => t('For Enable Titles'),
      '#default_value' => $record['status'],
      '#prefix' => '<div class="check-box">',
      '#suffix' => '</div>',
    ];

    $form['icon'] = [
      '#type' => 'select',
      '#title' => ('Icon'),
      '#options' => [
        'Icon1' => t('Icon1'),
        'Icon2' => t('Icon2'),
      ],
    ];
    $form['id'] = [
      '#type' => 'hidden',
      '#default_value' => $id,
    ];
    $form['actions']['#type'] = 'actions';

    $form['actions']['back'] = [
      '#type' => 'markup',
      '#value' => $this->t('Back'),
      '#markup' => '<a href="/manage/tiles" class="use-ajax reserve"  data-dialog-options="{&quot;width&quot;:800}" data-dialog-type="modal"> Back </a>',
    ];
    $form['actions']['update'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getValue('id');
    $enable = $form_state->getValue('enable');
    $num_updated = \Drupal::database()->update('dashboard_manage')
      ->fields([
        'status' => $enable,
      ])
      ->condition('id', $id, '=')
      ->execute();
    \Drupal::messenger()->addMessage('Updated record successfully');
    $response = new RedirectResponse("/");
    $response->send();
  }

}
