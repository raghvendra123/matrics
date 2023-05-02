<?php

namespace Drupal\entity_view_mode_normalize\Normalizer;

use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\serialization\Normalizer\FieldItemNormalizer;


/**
 * Class Paragraph Normalizer.
 *
 * @package Drupal\entity_view_mode_normalize\EntityReferenceRevisionsFieldItemListNormalizer
 */
class EntityReferenceRevisionsFieldItemListNormalizer extends FieldItemNormalizer {

  use CardinalityItemTrait;

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = EntityReferenceRevisionsFieldItemList::class;

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    if (!is_object($data) || !$this->checkFormat($format)) {
      return FALSE;
    }
    if ($data instanceof EntityReferenceRevisionsFieldItemList) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $entities = $object->referencedEntities();
    foreach ($entities as &$entity) {
      if ($entity->hasTranslation($language)) {
        $entity = $entity->getTranslation($language);
      }
    }
    $attributes = $this->serializer->normalize($entities, $format, $context);
    return $this->getItemByCardinalityContext($attributes, $context);
  }

}
