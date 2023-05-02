<?php

namespace Drupal\entity_view_mode_normalize\Normalizer;

use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\serialization\Normalizer\ComplexDataNormalizer;
use Drupal\serialization\Normalizer\FieldItemNormalizer;


/**
 * Class Paragraph Normalizer.
 *
 * @package Drupal\entity_view_mode_normalize\EntityReferenceFieldItemListNormalizer
 */
class EntityReferenceFieldItemListNormalizer extends ComplexDataNormalizer {

  use CardinalityItemTrait;

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = EntityReferenceFieldItemList::class;

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    if (!is_object($data) || !$this->checkFormat($format)) {
      return FALSE;
    }
    if ($data instanceof EntityReferenceFieldItemList) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $attributes = [];
    $entities = $object->referencedEntities();
    $normalize_entity = FALSE;
    if (!empty($context['field']['type']) && $context['field']['type'] == 'entity_reference_entity_view') {
      $normalize_entity = TRUE;
    }
    else {
      if (empty($context['view_mode'])) {
        $context['view_mode'] = 'default';
      }
      foreach ($object as $item) {
        $field_view = $item->view($context['view_mode']);
        if (!empty($field_view['#plain_text'])) {
          $attributes[] = $field_view['#plain_text'];
        }
        elseif (!empty($field_view['#title']) && !empty($field_view['#url'])) {
          $attributes[] = [
            'title' => $field_view['#title'],
            'url' => $field_view['#url']->toString(),
          ];
        }
      }
      if (empty($attributes)) {
        $normalize_entity = TRUE;
      }
    }

    if ($normalize_entity == TRUE) {
      $attributes = $this->serializer->normalize($entities, $format, $context);
    }

    return $this->getItemByCardinalityContext($attributes, $context);
  }

}
