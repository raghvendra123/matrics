<?php

namespace Drupal\entity_view_mode_normalize\Plugin\views\row;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rest\Plugin\views\row\DataEntityRow;
use Drupal\views\Entity\Render\EntityTranslationRenderTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Plugin which displays entities as raw data.
 *
 * @ingroup views_row_plugins
 *
 * @ViewsRow(
 *   id = "view_mode_data_entity",
 *   title = @Translation("Entity (with view mode)"),
 *   help = @Translation("Use entities as row data."),
 *   display_types = {"data"}
 * )
 */
class ViewModeDataEntityRow extends DataEntityRow {

  use EntityTranslationRenderTrait;

  /**
   * The serializer which serializes the views result.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    EntityRepositoryInterface $entity_repository,
    EntityDisplayRepositoryInterface $entity_display_repository = NULL,
    SerializerInterface $serializer
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_type_manager,
      $language_manager,
      $entity_repository
    );
    $this->entityDisplayRepository = $entity_display_repository;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('entity.repository'),
      $container->get('entity_display.repository'),
      $container->get('serializer')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected $usesOptions = TRUE;

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($this->getEntityTypeId()),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->options['view_mode'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    $render = parent::render($row);
    $context = [];
    $context['field']['settings']['view_mode'] = $this->options['view_mode'];
    $context['view_row_plugin'] = $this->getPluginId();

    if ((empty($this->view->live_preview))) {
      $content_type = $this->displayHandler->getContentType();
    }
    else {
      $formats = [];
      if (!empty($this->view->getDisplay()->display['display_options']['style']['options']['formats'])) {
        $formats = $this->view->getDisplay()->display['display_options']['style']['options']['formats'];
      }
      $content_type = !empty($formats) ? reset($formats) : 'json';
    }

    return $this->serializer->normalize($render, $content_type, $context);
  }

}
