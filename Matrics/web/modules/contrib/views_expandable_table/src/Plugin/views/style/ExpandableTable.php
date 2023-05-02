<?php

namespace Drupal\views_expandable_table\Plugin\views\style;

use Drupal\views\Plugin\views\style\Table;

/**
 * Style plugin to render each item as a row in an expandable table.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "views_view_expandabletable",
 *   title = @Translation("Expandable Table"),
 *   help = @Translation("Displays rows in an expandable table."),
 *   theme = "views_view_expandabletable",
 *   display_types = {"normal"}
 * )
 */
class ExpandableTable extends Table {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#attached']['library'][] = 'views_expandable_table/expandable_table';
    return $build;
  }

}
