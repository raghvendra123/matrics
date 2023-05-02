<?php

namespace Drupal\entity_view_mode_normalize\Normalizer;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\serialization\Normalizer\TypedDataNormalizer;

/**
 * Select List Field Item List Normalizer.
 */
class SelectListFieldItemListNormalizer extends TypedDataNormalizer {
  use CardinalityItemTrait;

  /**
   * The country_manager service.
   *
   * @var \Drupal\Core\Locale\CountryManagerInterface
   */
  protected $countryManager;

  /**
   * Constructs a PhoneNumberFieldItemListNormalizer object.
   *
   * @param \Drupal\Core\Locale\CountryManagerInterface $countryManager
   *   The country_manager service.
   */
  public function __construct(CountryManagerInterface $countryManager) {
    $this->countryManager = $countryManager;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // If we aren't dealing with an object or the format is not supported return
    // now.
    if (!is_object($data) || !$this->checkFormat($format)) {
      return FALSE;
    }

    if ($data instanceof FieldItemListInterface) {
      $field_definition = $data->getFieldDefinition();
      $type = $field_definition->getType();
      if (in_array($type, [
        'list_float',
        'list_integer',
        'list_string',
        'address_country',
      ])) {
        return TRUE;
      }

    }
    return FALSE;

  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {

    $value = [];
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */
    $field_definition = $object->getFieldDefinition();
    $type = $field_definition->getType();
    $field_values = $object->getValue();
    if (empty($field_values)) {
      $value['selected'] = NULL;
    }
    else {
      foreach ($field_values as $field_value) {
        $value['selected'][] = $field_value['value'];
      }
    }

    if ($type == "address_country") {
      $available_country = $this->countryManager->getList();
      foreach ($available_country as &$country) {
        $country = $country->render();
      }
      $value['options'] = $available_country;
    }
    else {
      $field_storage_definition = $field_definition->getFieldStorageDefinition();
      $allowed_values = $field_storage_definition->getSetting('allowed_values');
      $value['options'] = $allowed_values;
    }

    return $this->getItemByCardinalityContext($value, $context);
  }

}
