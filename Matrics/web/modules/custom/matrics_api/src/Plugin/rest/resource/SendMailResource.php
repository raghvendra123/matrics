<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Provides a Send Mail Resource.
 *
 * @RestResource(
 *   id = "mail",
 *   label = @Translation("Send Mail"),
 *   uri_paths = {
 *     "canonical" = "/mail/trigger",
 *     "create" = "/mail/trigger"
 *   }
 * )
 */
class SendMailResource extends ResourceBase {

  /**
   * A current user instance which is logged in the session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $config
   *   A configuration array which contains the information about the plugin instance.
   * @param string $module_id
   *   The module_id for the plugin instance.
   * @param mixed $module_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A currently logged user instance.
   */
  public function __construct(
    array $config,
    $module_id,
    $module_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user
  ) {
    parent::__construct(
      $config,
      $module_id,
      $module_definition,
      $serializer_formats,
      $logger
    );
    $this->loggedUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $config, $module_id, $module_definition) {
    return new static(
      $config,
      $module_id,
      $module_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('matrics_api'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {
    $id = \Drupal::request()->query->get('id');
    $ids = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->execute();
    $users = User::loadMultiple($ids);

    foreach ($users as $user) {
      $username = $user->get('field_employee_name')->getString();
      $mail = $user->get('mail')->getString();
      $userlist[$mail] = $username;
    }
    $response = [$userlist, $id];
    $code = 200;
    return new ResourceResponse($response, $code);
  }

  /**
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    $id = $data['id'];
    $node = Node::load($id);
    $csv_file = $node->get('field_file')->getValue()[0]['target_id'];
    $file = File::load($csv_file);
    $path = '
    <p>Hello,</p>
    <p>You can download report using below link</p>
    <a href="' . file_create_url($file->getFileUri()) . '">' . file_create_url($file->getFileUri()) . '</a>';

    $user_list = $data['user_list'];
    $to = "kevinpatidar@agile451.com ,";
    foreach ($user_list as $value) {
      $to .= $value . " ,";
    }
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'matrics_reports';
    $key = 'report_email';
    $params['message'] = $path;
    $params['title'] = 'Report File';
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] !== TRUE) {
      $response = ['message' => 'There was a problem sending your message and it was not sent.'];
      $code = 200;
    }
    else {
      $response = ['message' => 'Your message has been sent.'];
      $code = 200;
    }
    return new ResourceResponse($response, $code);
  }

}
