<?php

namespace Drupal\entity_view_mode_normalize\Form;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityType;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SimpleEntitySerializeConfigForm.
 */
class SimpleEntitySerializeConfigForm extends ConfigFormBase {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'entity_view_mode_normalize.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_view_mode_normalize_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('entity_view_mode_normalize.settings');

    dump($this->getContentEntityType());


    return parent::buildForm($form, $form_state);
  }

  public function getContentEntityType() {
    $definitions = \Drupal::entityTypeManager()->getDefinitions();
    $options = [];
    foreach ($definitions as $key => $definition) {
      if ($definition instanceof ContentEntityType) {
        $options[$key] = $definition;
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('entity_view_mode_normalize.settings')
      ->save();
  }

}
