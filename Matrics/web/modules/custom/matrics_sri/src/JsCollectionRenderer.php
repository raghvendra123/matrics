<?php

namespace Drupal\matrics_sri;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Asset\JsCollectionRenderer as JsCollectionRendererOrigin;

/**
 * Class JsCollectionRenderer.
 *
 * @package Drupal\matrics_sri
 */
class JsCollectionRenderer extends JsCollectionRendererOrigin {

  /**
   * {@inheritdoc}
   */
  public function render(array $js_assets) {
    $elements = [];

    // A dummy query-string is added to filenames, to gain control over
    // browser-caching. The string changes on every update or full cache
    // flush, forcing browsers to load a new copy of the files, as the
    // URL changed. Files that should not be cached get REQUEST_TIME as
    // query-string instead, to enforce reload on every page request.
    $default_query_string = $this->state->get('system.css_js_query_string', '0');

    // Defaults for each SCRIPT element.
    $element_defaults = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => '',
    ];

    // Loop through all JS assets.
    foreach ($js_assets as $js_asset) {
      // Element properties that do not depend on JS asset type.
      $element = $element_defaults;
      $element['#browsers'] = $js_asset['browsers'];

      // Element properties that depend on item type.
      switch ($js_asset['type']) {
        case 'setting':
          $element['#attributes'] = [
            // This type attribute prevents this from being parsed as an
            // inline script.
            'type' => 'application/json',
            'data-drupal-selector' => 'drupal-settings-json',
          ];
          $element['#value'] = Json::encode($js_asset['data']);
          break;

        case 'file':
          $query_string = $js_asset['version'] == -1 ? $default_query_string : 'v=' . $js_asset['version'];
          $query_string_separator = (strpos($js_asset['data'], '?') !== FALSE) ? '&' : '?';
          $element['#attributes']['src'] = file_url_transform_relative(file_create_url($js_asset['data']));
          // Only add the cache-busting query string if this isn't an aggregate
          // file.
          if (!isset($js_asset['preprocessed'])) {
            $request_time = \Drupal::time()->getRequestTime();
            $element['#attributes']['src'] .= $query_string_separator . ($js_asset['cache'] ? $query_string : $request_time);
          }
          break;

        case 'external':
          $element['#attributes']['src'] = $js_asset['data'];
          if (isset($js_asset['integrity'])) {
            $element['#attributes']['integrity'] = $js_asset['integrity'];
          }
          if (isset($js_asset['crossorigin'])) {
            $element['#attributes']['crossorigin'] = $js_asset['crossorigin'];
          }
          break;

        default:
          throw new \Exception('Invalid JS asset type.');
      }

      // Attributes may only be set if this script is output independently.
      if (!empty($element['#attributes']['src']) && !empty($js_asset['attributes'])) {
        $element['#attributes'] += $js_asset['attributes'];
      }

      $elements[] = $element;
    }

    return $elements;
  }

}
