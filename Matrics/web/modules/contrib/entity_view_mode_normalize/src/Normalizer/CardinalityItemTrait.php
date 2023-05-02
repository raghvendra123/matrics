<?php

namespace Drupal\entity_view_mode_normalize\Normalizer;

/**
 * Return single value or array depend on context key cardinality.
 *
 * @package Drupal\entity_view_mode_normalize\Normalizer
 */
trait CardinalityItemTrait {

  /**
   * Get item by cardinality.
   *
   * @param array $attributes
   *   Array of items.
   * @param array $context
   *   Array context.
   *
   * @return array|mixed|null
   *   Return array or a item.
   */
  public function getItemByCardinalityContext(array $attributes, array $context) {
    if (!empty($context['cardinality']) && $context['cardinality'] == 1) {
      if (!empty($attributes[0]) && is_array($attributes[0]) && array_key_exists('value', $attributes[0])) {
        return $attributes[0]['value'];
      }
      if (!empty($attributes[0])) {
        return $attributes[0];
      }
    }
    return empty($attributes) ? NULL : $attributes;
  }

}
