<?php

namespace Drupal\matrics_course_booking\Controller;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Xss;
use Drupal\node\Entity\Node;

/**
 * Class json controller.
 */
class JsonApiArticlesController {

  /**
   * Function auto complete.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function handleAutocomplete(Request $request) {
    $results = [];
    $input = $request->query->get('q');
    if (!$input) {
      return new JsonResponse($results);
    }
    $input = Xss::filter($input);
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'courses')
      ->condition('title', $input, 'CONTAINS')
      ->groupBy('nid')
      ->sort('created', 'DESC')
      ->range(0, 10);
    $ids = $query->execute();
    $nodes = $ids ? Node::loadMultiple($ids) : [];
    foreach ($nodes as $node) {
      $results[] = [
        'value' => EntityAutocomplete::getEntityLabels([$node]),
        'label' => $node->getTitle() . ' (' . $node->id() . ')',
      ];
    }
    return new JsonResponse($results);
  }

  /**
   * {@inheritdoc}
   */
  public function ProviderAutocomplete(Request $request) {
    $results = [];
    $input = $request->query->get('q');
    if (!$input) {
      return new JsonResponse($results);
    }
    $input = Xss::filter($input);
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'vendor')
      ->condition('title', $input, 'CONTAINS')
      ->groupBy('nid')
      ->sort('created', 'DESC')
      ->range(0, 10);
    $ids = $query->execute();
    $nodes = $ids ? Node::loadMultiple($ids) : [];
    foreach ($nodes as $node) {
      $results[] = [
        'value' => EntityAutocomplete::getEntityLabels([$node]),
        'label' => $node->getTitle() . ' (' . $node->id() . ')',
      ];
    }
    return new JsonResponse($results);
  }

}
