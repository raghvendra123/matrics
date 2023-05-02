<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\user\Entity\User;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Provides a Book Course Resource.
 *
 * @RestResource(
 *   id = "book_course",
 *   label = @Translation("Book Course "),
 *   uri_paths = {
 *     "create" = "/book_courses"
 *   }
 * )
 */
class BookCourseResource extends ResourceBase {

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
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $data) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $msg = ['message' => $this->t('Please Post cname, pname, location and date key.')];
    $code = 400;
    if (!empty($data)) {
      $uid = $data['uid'];
      $accr = $data['accr'];
      $user = User::load($uid);
      $address = [
        'country_code' => $data['country'],
        'locality' => $data['city'],
      ];
      $paragraph = Paragraph::create([
        'type' => 'course',
        'field_course_' => $data['field_course_'],
        'field_provider_name' => $data['field_provider_name'],
        'field_location' => $address,
        'field_city' => $data['city'],
        'field_start_date' => $data['field_start_date'],
        'field_expiry_date' => $data['field_expiry_date'],
        'field_list_price' => $data['field_list_price'],
        'field_discount_price' => $data['field_discount_price'],
        'field_comments' => $data['field_comments'],
        'field_certificate_id' => $data['cid'],
        'field_status' => 'course booked',
      ]);
      $paragraph->save();
      $user->field_course_details[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
      if ($user->save()) {
        $insert = \Drupal::database()->insert('notify_course')
          ->fields([
            'user_id' => $uid,
            'pid' => $paragraph->id(),
            'accreditation' => $accr,
            'type' => 'booked_course',
          ])
          ->execute();
        $msg = ['message' => $this->t('Booked course successfully.')];
      }
      else {
        $msg = ['message' => $this->t('Course not booked, please try again.')];
      }
      $code = 200;
    }
    $response = new ResourceResponse($msg, $code);
    $response->addCacheableDependency($msg);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => [
          'rebuild-events-feed',
        ],
      ],
    ]));
    return $response;
  }

}
