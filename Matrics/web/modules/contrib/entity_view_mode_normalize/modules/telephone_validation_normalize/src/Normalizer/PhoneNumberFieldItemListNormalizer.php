<?php

namespace Drupal\telephone_validation_normalize\Normalizer;

use Drupal\Core\Config\Entity\ThirdPartySettingsInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Locale\CountryManagerInterface;
use Drupal\entity_view_mode_normalize\Normalizer\CardinalityItemTrait;
use Drupal\serialization\Normalizer\TypedDataNormalizer;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

/**
 * Phone Number with E164 validation Field Item List Normalizer.
 */
class PhoneNumberFieldItemListNormalizer extends TypedDataNormalizer {

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
      if ($field_definition instanceof ThirdPartySettingsInterface) {
        $settings = $field_definition->getThirdPartySettings('telephone_validation');
        return !empty($settings);
      }
    }
    return FALSE;

  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $telephone_field_values = $object->getValue();
    foreach ($telephone_field_values as $telephone_field_value) {
      $telephone_value = $telephone_field_value['value'];
      $phoneUtils = PhoneNumberUtil::getInstance();
      try {
        $number = $phoneUtils->parse($telephone_value);
        $country_code = $number->getCountryCode();
        $phone_number_value = $number->getNationalNumber();
      }
      catch (NumberParseException $exception) {
      }
      $regions = [];
      foreach ($this->countryManager->getList() as $region => $name) {
        $country_phone_code = $phoneUtils->getCountryCodeForRegion($region);
        $country_name = $name->render();
        $regions[] = [
          "short_name" => $region,
          "name" => $country_name,
          "code" => $country_phone_code,
        ];
      }
      $value[] = [
        'country_code' => $country_code ?? NULL,
        'nation_phone_number' => $phone_number_value ?? NULL,
        'list_country_code' => $regions,
      ];
    }
    if (empty($value)) {
      $value = parent::normalize($object, $format, $context);
    }

    return $this->getItemByCardinalityContext($value, $context);
  }

}
