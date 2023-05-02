<?php

namespace Drupal\matrics_dashboard\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines a form that configures forms module settings.
 */
class SiteConfigurationForm extends ConfigFormBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a new SiteConfiguration Form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);

    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'matrics_workflows_site_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'matrics_dashboard.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('matrics_dashboard.settings');

    $form['login'] = [
      '#type'  => 'details',
      '#open'  => FALSE,
      '#title' => $this->t('Login Page'),
    ];

    $form['login']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('This is used for title.'),
      '#default_value' => $config->get('title'),
    ];

    $form['login']['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('This is used for description.'),
      '#default_value' => $config->get('description'),
      '#maxlength' => 1000,
    ];

    $form['login']['image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Background Image'),
      '#description' => $this->t('This is used for background image'),
      '#default_value' => $config->get('image'),
      '#upload_location' => 'public://',
    ];

    $form['login']['link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact Us Link'),
      '#description' => $this->t('This is used for contact us link'),
      '#default_value' => $config->get('link'),
      '#maxlength' => 255,
    ];

    $form['login']['forgot_password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Forgot Password Link'),
      '#description' => $this->t('This is used for Forgot Password link'),
      '#default_value' => $config->get('forgot_password'),
      '#maxlength' => 255,
    ];

    $form['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Version'),
      '#default_value' => $config->get('version'),
      '#maxlength' => 255,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!empty($form_state->getValue('image'))) {
      $image = $form_state->getValue('image');
      $file = $this->entityTypeManager->getStorage('file')->load($image[0]);
      $file->setPermanent();
      $file->save();
    }

    $this->config('matrics_dashboard.settings')
      ->set('image', $form_state->getValue('image'))
      ->set('title', $form_state->getValue('title'))
      ->set('description', $form_state->getValue('description'))
      ->set('link', $form_state->getValue('link'))
      ->set('forgot_password', $form_state->getValue('forgot_password'))
      ->set('version', $form_state->getValue('version'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
