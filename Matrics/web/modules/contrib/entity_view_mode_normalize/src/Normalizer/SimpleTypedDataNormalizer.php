<?php

namespace Drupal\entity_view_mode_normalize\Normalizer;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\serialization\Normalizer\TypedDataNormalizer;

/**
 * Converts typed data objects to arrays.
 */
class SimpleTypedDataNormalizer extends TypedDataNormalizer {

  use CardinalityItemTrait;

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\TypedData\TypedDataInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {

    if (empty($context['view_mode'])) {
      return parent::normalize($object, $format, $context);
    }
    $value = $this->getViewFieldValue($object, $context);

    if (empty($value)) {
      $value = parent::normalize($object, $format, $context);
    }

    return $this->getItemByCardinalityContext($value, $context);
  }

  /**
   * Get value from view mode.
   *
   * @param mixed $object
   *   Object field.
   * @param array $context
   *   Context of normalize.
   *
   * @return array
   *   Data rendered.
   */
  public function getViewFieldValue($object, array $context) {
    $value = [];
      //dump($object->getName() == 'description');
    foreach ($object as $item) {
      $field_view = $item->view($context['view_mode']);

      if (!empty($field_view['#text'])) {
        $value[] = $field_view['#text'];
      }
      elseif (!empty($field_view['#markup'])) {
        $value[] = $field_view['#markup'];
      }
      elseif (!empty($field_view['#context']['value'])) {
        $value[] = $field_view['#context']['value'];
      }
      elseif (!empty($field_view['#url'])) {
        $value[] = [
          'url' => $field_view['#url']->toString(),
          'title' => $field_view['#title'],
        ];
      }
    }
    return $value;
  }

}
