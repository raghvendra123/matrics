<?php

namespace Drupal\entity_view_mode_normalize\Normalizer;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TypedDataInternalPropertiesHelper;
use Drupal\serialization\Normalizer\EntityNormalizer;

/**
 * Converts typed data objects to arrays.
 */
class SimpleEntityNormalizer extends EntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Entity\EntityInterface';

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    if (!is_object($data) || !$this->checkFormat($format)) {
      return FALSE;
    }
    if ($data instanceof EntityInterface) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $entity_type = $object->getEntityTypeId();
    $bundle = $object->bundle();
    $view_mode = \Drupal::request()->get('_view_mode');
    if (!empty($context['field']['settings']['view_mode'])) {
      $view_mode = $context['field']['settings']['view_mode'];
    }
    $entity_view_display = EntityViewDisplay::load("{$entity_type}.{$bundle}.{$view_mode}");
    if (empty($entity_view_display)) {
      $view_mode = 'default';
      $entity_view_display = EntityViewDisplay::load("{$entity_type}.{$bundle}.{$view_mode}");
    }
    if (empty($entity_view_display)) {
      return parent::normalize($object, $format, $context);
    }

    $context['view_mode'] = $view_mode;
    $context['entity_type'] = $entity_type;
    $context['bundle'] = $entity_type;
    $fields = $entity_view_display->get('content');
    $attributes = [];
    if ($object instanceof ComplexDataInterface) {
      // If there are no properties to normalize, just normalize the value.
      $object = !empty($object->getProperties(TRUE))
        ? TypedDataInternalPropertiesHelper::getNonInternalProperties($object)
        : $object->getValue();
    }
    $config_fields = \Drupal::service('entity_field.manager')
      ->getFieldDefinitions($entity_type, $bundle);
    /** @var \Drupal\Core\TypedData\TypedDataInterface $property */
    foreach ($fields as $name => $field) {
      $context['field'] = $field;
      if (!empty($config_fields[$name])) {
        $context['cardinality'] = $config_fields[$name]->getFieldStorageDefinition()
          ->getCardinality();
      }
      if (empty($object->$name)) {
        continue;
      }
      $attributes[$name] = $this->serializer->normalize($object->$name, $format, $context);
    }
    return $attributes;
  }

}
