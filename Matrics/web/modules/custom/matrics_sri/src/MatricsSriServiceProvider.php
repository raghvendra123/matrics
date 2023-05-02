<?php

namespace Drupal\matrics_sri;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class MatricsSriServiceProvider.
 *
 * @package Drupal\matrics_sri
 */
class MatricsSriServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('asset.js.collection_renderer');
    $definition->setClass('Drupal\matrics_sri\JsCollectionRenderer');
    $definition = $container->getDefinition('asset.css.collection_renderer');
    $definition->setClass('Drupal\matrics_sri\CssCollectionRenderer');
  }

}
