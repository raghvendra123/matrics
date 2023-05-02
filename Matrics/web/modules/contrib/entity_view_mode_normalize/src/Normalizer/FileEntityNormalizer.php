<?php

namespace Drupal\entity_view_mode_normalize\Normalizer;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TypedDataInternalPropertiesHelper;
use Drupal\file\FileInterface;
use Drupal\serialization\Normalizer\EntityNormalizer;

/**
 * Converts typed data objects to arrays.
 */
class FileEntityNormalizer extends EntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\file\FileInterface';

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    if (!is_object($data) || !$this->checkFormat($format)) {
      return FALSE;
    }
    if ($data instanceof FileInterface) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $attributes = parent::normalize($object, $format, $context);
    $attributes['url'] = file_url_transform_relative(file_create_url($object->getFileUri()));

    return $attributes;
  }

}
