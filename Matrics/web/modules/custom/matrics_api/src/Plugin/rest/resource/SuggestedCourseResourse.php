<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Provides a Suggested Course Resource.
 *
 * @RestResource(
 *   id = "suggestedcourse",
 *   label = @Translation("Suggested Course"),
 *   uri_paths = {
 *     "canonical" = "/suggestedcourse",
 *     "create" = "/suggestedcourse"
 *   }
 * )
 */
class SuggestedCourseResourse extends ResourceBase {

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
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $output = [];
    $uid = \Drupal::request()->query->get('uid');
    $certificate = \Drupal::request()->query->get('certificate');
    $query1 = \Drupal::database()->select('booked_course', 'n')
      ->fields('n')->condition('n.uid', $uid);
    $result = $query1->execute()->fetchAll();
    if (count($result) != 0) {
      foreach ($result as $val) {
        $output[$val->uid] = [
          'cname' => $val->course_name,
          'pname' => $val->provider_name,
          'location' => $val->location,
          'expiry' => $val->expiry_date,
        ];
      }
    }
    $response = new ResourceResponse($output);
    $response->addCacheableDependency($output);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => [
          'rebuild-events-feed',
        ],
      ],
    ]));
    return $response;
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
    $msg = ['message' => $this->t('Please Post course, provider_name, location and course_date key.')];
    $code = 400;
    if (!empty($data)) {
      $options = [];
      $options['select'] = '- select -';
      if (!empty($data)) {
        $uid = \Drupal::request()->query->get('uid');
        $accr = \Drupal::request()->query->get('accr');
        $exp = \Drupal::request()->query->get('exp');
        $certificate = \Drupal::request()->query->get('certificate');
        $data1[1]['course1'] = $data['course1'];
        $data1[1]['provider_name1'] = $data['provider_name1'];
        $data1[1]['location1'] = $data['location1'];
        $data1[1]['course_date1'] = $data['course_date1'];
        $data1[2]['course2'] = $data['course2'];
        $data1[2]['provider_name2'] = $data['provider_name2'];
        $data1[2]['location2'] = $data['location2'];
        $data1[2]['course_date2'] = $data['course_date2'];
        $data1[3]['course3'] = $data['course3'];
        $data1[3]['provider_name3'] = $data['provider_name3'];
        $data1[3]['location3'] = $data['location3'];
        $data1[3]['course_date3'] = $data['course_date3'];
        $serializer = \Drupal::service('serializer');
        $data_ser = $serializer->serialize($data1, 'json');
        $query = \Drupal::database()->select('notify_course', 'n')
          ->fields('n', ['user_id', 'accreditation', 'type'])->condition('n.user_id', $uid)->condition('n.type', 'suggested_course');
        $result = $query->countQuery()->execute()->fetchField();
        if ($result == 0) {
          $insert = \Drupal::database()->insert('notify_course')
            ->fields([
              'course_id' => $data_ser,
              'user_id' => $uid,
              'accreditation' => $accr,
              'expiry' => $exp,
              'certificate' => $certificate,
              'type' => 'suggested_course',
            ])
            ->execute();
          $msg = ['message' => $this->t('Suggestions sent.')];
        }
        else {
          $update = \Drupal::database()->update('notify_course')
            ->fields([
              'course_id' => $data_ser,
            ])
            ->condition('user_id', $uid)
            ->condition('type', 'suggested_course')
            ->execute();
          $msg = ['message' => $this->t('Suggestions sent.')];
        }
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
