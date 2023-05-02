<?php

namespace Drupal\matrics_dashboard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Dashboard settings for this site.
 */
class DashboardPersonalizedTitle extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'matrics_dashboard.title_settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'matrics_dashboard_title_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config(static::SETTINGS);
    $uid = \Drupal::currentUser()->id();
    $form['my_dashboard_' . $uid] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dashboard title'),
      '#default_value' => $config->get('my_dashboard_' . $uid),
    ];
    $form['actions']['submit']['#value'] = $this->t('Update');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    parent::submitForm($form, $form_state);
    $uid = \Drupal::currentUser()->id();
    $this->config(static::SETTINGS)
      ->set('my_dashboard_' . $uid, $form_state->getValue('my_dashboard_' . $uid))
      ->save();
    $form_state->setRedirect('matrics_dashboard.dashboard');
  }

}
