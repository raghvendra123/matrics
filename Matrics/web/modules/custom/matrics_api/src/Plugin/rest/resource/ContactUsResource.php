<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a Contact Us Resource.
 *
 * @RestResource(
 *   id = "contactus",
 *   label = @Translation("Contact Us"),
 *   uri_paths = {
 *     "create" = "/api/contactus"
 *   }
 * )
 */
class ContactUsResource extends ResourceBase {

  /**
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'matrics_course_booking';
    $key = 'user_mail_custom';
    $to = $data['mail'];
    $params['message'] = $data['message'];
    $params['subject'] = $data['subject'];
    // $contact = \Drupal::EntityTypeManager()->getStorage('contact_message');
    // $params['subject'] = $contact;
    $langcode = 'en';
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      $message = t('There was a problem sending your email notification to @email.', ['@email' => $to]);
      \Drupal::logger('mail-log')->error($message);
    }
    else {
      $message = t('An email notification has been sent to @email', ['@email' => $to]);
      \Drupal::logger('mail-log')->notice($message);
    }

    $response = ['message' => $message];
    $code = 200;

    return new ResourceResponse($response, $code);
  }

}
