<?php

namespace Drupal\entity_view_mode_normalize\Normalizer;

use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\serialization\Normalizer\ListNormalizer;


/**
 * Class Paragraph Normalizer.
 *
 * @package Drupal\entity_view_mode_normalize\EntityReferenceFieldItemListNormalizer
 */
class FileFieldItemListNormalizer extends SimpleTypedDataNormalizer {

  use CardinalityItemTrait;

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = FileFieldItemList::class;

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    if (!is_object($data) || !$this->checkFormat($format)) {
      return FALSE;
    }
    if ($data instanceof FileFieldItemList) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {

    $value = $this->getViewFieldValue($object, $context);
    if (empty($value)) {
      $referenced = $object->referencedEntities();
      $value = $this->serializer->normalize($referenced, $format, $context);
    }
    return $this->getItemByCardinalityContext($value, $context);
  }

}
