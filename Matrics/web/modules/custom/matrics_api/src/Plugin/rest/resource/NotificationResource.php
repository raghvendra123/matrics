<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\user\Entity\User;

/**
 * Provides a Notification Resource.
 *
 * @RestResource(
 *   id = "notification",
 *   label = @Translation("Notification"),
 *   uri_paths = {
 *     "canonical" = "/notification"
 *   }
 * )
 */
class NotificationResource extends ResourceBase {

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
    $query = \Drupal::database()->select('notification_status', 'n')
      ->fields('n')->condition('n.uid', \Drupal::currentUser()->id())->condition('n.status', 1);
    $result = $query->countQuery()->execute()->fetchField();
    $resultAll = $query->execute()->fetchObject();
    if ($result == 0) {
      $notification = $this->notification();
    }
    else {
      $notification = $this->notification($resultAll->created);
    }

    $code = 200;
    $response = new ResourceResponse(['notification' => $notification], $code);
    $response->addCacheableDependency(['notification' => $notification]);
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
   * {@inheritdoc}
   */
  public function notification($created = NULL) {
    $suggested_count = 0;
    $suggested_count1 = 0;
    $suggested = 0;
    $suggested1 = 0;
    $output = [];
    $query = \Drupal::database()->select('notify_course', 'n')
      ->fields('n')->condition('n.user_id', \Drupal::currentUser()->id())->orderBy('id', 'DESC')->range(0, 10);
    if ($created != NULL) {
      $query->condition('n.created', $created, '<=');
    }
    $result = $query->countQuery()->execute()->fetchField();
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    $count = 0;
    if ($result != 0) {
      $query1 = \Drupal::database()->select('notify_course', 'n')
        ->fields('n')->condition('n.status', 0)->range(0, 10);
      $query1->condition('n.user_id', \Drupal::currentUser()->id());
      if ($created != NULL) {
        $query1->condition('n.created', $created, '<=');
      }
      $result1 = $query1->countQuery()->execute()->fetchField();
      $resultAll = $query->execute()->fetchAll();
      $suggested_count = $result1;
      $suggested = $result;

      $query2 = \Drupal::database()->select('booked_course', 'b');
      $query2->fields('b');
      $query2->condition('b.uid', \Drupal::currentUser()->id())->range(0, 10);
      $result2 = $query2->countQuery()->execute()->fetchField();
      foreach ($resultAll as $result) {
        if ($result2 == 0) {
          $width = "{&quot;width&quot;:1400}";
        }
        else {
          $width = "{&quot;width&quot;:400}";
        }
        if ($result->type == 'booked_course') {
          $p = Paragraph::load($result->pid);
          $title = \Drupal::database()->select('node_field_data', 'n')->fields('n', ['title'])->condition('n.nid', $p->get('field_course_')->getValue()[0]['target_id'])->execute()->fetchObject();
          $output[$count]['id'] = $result->id;
          $output[$count]['msg'] = "Your course " . $title->title . " for accreditation " . $result->accreditation . " has been booked and will take place on " . $p->get('field_start_date')->getValue()[0]['value'] . " at " . $p->get('field_location')->getValue()[0]['locality'] . ". Joining instructions will be provided via joining instruction";
          $output[$count]['type'] = $result->type;
          $output[$count]['created'] = $result->created;
          $output[$count]['url'] = '/booked_course_detail/' . $result->user_id . '/' . $p->get('field_course_')->getValue()[0]['target_id'];
        }
        elseif ($result->type == 'suggested_course') {
          $output[$count]['id'] = $result->id;
          $output[$count]['msg'] = "Your " . $result->accreditation . " will expire on " . $result->expiry . " please select a course to renew your accreditation.";
          $output[$count]['type'] = $result->type;
          $output[$count]['created'] = $result->created;
          $output[$count]['url'] = '/suggested_course_list/' . $result->id;
        }
        elseif ($result->type == 'reminder_course') {
          $output[$count]['id'] = $result->id;
          $output[$count]['msg'] = "Your course will start after 7 days.";
          $output[$count]['type'] = $result->type;
          $output[$count]['created'] = $result->created;
          $output[$count]['url'] = '/user/' . $result->user_id;
        }
        $count++;
      }
    }
    if (in_array("mnager", $roles)) {
      $query = \Drupal::database()->select('notification', 'n')
        ->fields('n')->orderBy('id', 'DESC');
      $result = $query->countQuery()->execute()->fetchField();
      if ($result != 0) {
        $query1 = \Drupal::database()->select('notification', 'n')
          ->fields('n')->condition('n.status', 0);
        $result1 = $query1->countQuery()->execute()->fetchField();
        $resultAll = $query->execute()->fetchAll();
        $suggested_count1 = $result1;
        $suggested1 = $result;

        foreach ($resultAll as $result) {
          $user = User::load($result->uid);
          $output[$count]['id'] = $result->id;
          $output[$count]['msg'] = "Book " . $result->course_name . " for " . $user->getDisplayName();
          $count++;
        }
      }
    }
    $notification['suggested_count'] = $suggested_count + $suggested_count1;
    $notification['suggested'] = $suggested + $suggested1;
    $notification['suggested_msg'] = $output;

    return $notification;
  }

}
