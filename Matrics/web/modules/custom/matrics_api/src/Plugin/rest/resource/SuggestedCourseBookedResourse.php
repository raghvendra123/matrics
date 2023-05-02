<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\Component\Serialization\Json;

/**
 * Provides a Suggested Course Booked Resource.
 *
 * @RestResource(
 *   id = "suggestedcoursebooked",
 *   label = @Translation("Suggested Course Booked"),
 *   uri_paths = {
 *     "canonical" = "/suggested_course_list/{id}",
 *     "create" = "/suggested_course_booked/{id}"
 *   }
 * )
 */
class SuggestedCourseBookedResourse extends ResourceBase {

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
  public function get($id) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $output = [];
    $current_user = \Drupal::currentUser()->id();
    $query = \Drupal::database()->select('booked_course', 'b');
    $query->fields('b');
    $query->condition('b.uid', $current_user);

    $counts = $query->countQuery()->execute()->fetchField();
    if ($counts == 0) {
      $booked = FALSE;
      $query1 = \Drupal::database()->select('notify_course', 'c');
      $query1->fields('c', ['course_id', 'user_id', 'certificate']);
      $query1->condition('c.id', $id);
      $result = $query1->execute()->fetchAll();
      $index = 0;

      foreach ($result as $keys => $value) {
        $certificate = $value->certificate;
        $courses = get_courses_by_certificate($value->certificate);
        $data = Json::decode($value->course_id);
        $rowkey = 0;
        foreach ($data as $key => $val) {
          if ($rowkey == 0) {
            $course_id = $val['course1'];
            $provider_id = $val['provider_name1'];
            $location = $val['location1'];
            $course_date = $val['course_date1'];
          }
          elseif ($rowkey == 1) {
            $course_id = $val['course2'];
            $provider_id = $val['provider_name2'];
            $location = $val['location2'];
            $course_date = $val['course_date2'];
          }
          elseif ($rowkey == 2) {
            $course_id = $val['course3'];
            $provider_id = $val['provider_name3'];
            $location = $val['location3'];
            $course_date = $val['course_date3'];
          }
          if (!empty($course_id)) {
            $course = \Drupal::database()->select('node_field_data', 'n')
              ->fields('n', ['title'])
              ->condition('n.nid', $course_id)->execute()->fetch();
            $provider_name = \Drupal::database()->select('node_field_data', 'n')
              ->fields('n', ['title'])
              ->condition('n.nid', $provider_id)->execute()->fetch();
            $output[$index]['course_name'] = $course->title;
            $output[$index]['provider_name'] = $provider_name->title;
            $output[$index]['location'] = $location;
            $output[$index]['expiry_date'] = $course_date;
            $output[$index]['course_id'] = (int) $course_id;
            if (!empty($course_id)) {
              unset($courses[$course_id]);
            }
            $index++;
          }
          $rowkey++;
        }
      }

      foreach ($courses as $keys => $val) {
        if (!empty($keys)) {
          $providers = get_providers_by_course($certificate, $keys);
          $course = \Drupal::database()->select('node_field_data', 'n')
            ->fields('n', ['title'])
            ->condition('n.nid', $keys)->execute()->fetch();
          $provider_id = array_key_last($providers);
          $provider_name = \Drupal::database()->select('node_field_data', 'n')
            ->fields('n', ['title'])
            ->condition('n.nid', $provider_id)->execute()->fetch();
          $output[$index]['course_name'] = $course->title;
          $output[$index]['provider_name'] = $provider_name->title;
          $output[$index]['location'] = 'London';
          $output[$index]['expiry_date'] = date('Y-m-d');
          $output[$index]['course_id'] = $keys;
          unset($courses[$key]);
          $index++;
        }
      }
    }
    else {
      $booked = TRUE;
      $result = $query->execute()->fetchAll();
      foreach ($result as $key => $val) {
        $output[$key]['course_name'] = $val->course_name;
        $output[$key]['provider_name'] = $val->provider_name;
        $output[$key]['location'] = $val->location;
        $output[$key]['expiry_date'] = $val->expiry_date;
        $output[$key]['course_id'] = 0;
      }
    }
    $result = [
      'counts' => $counts,
      'booked' => $booked,
      'rows' => $output,
    ];
    $response = new ResourceResponse($result, 200);
    $response->addCacheableDependency($result);
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
  public function post($id, array $data) {
    if (!$this->loggedUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }
    $msg = ['message' => $this->t('Please Post cname, pname, location and date key.')];
    $code = 400;
    if (!empty($data)) {
      $account = User::load(\Drupal::currentUser()->id());
      $username = $account->getDisplayName();
      $cname = $data['cname'];
      $pname = $data['pname'];
      $loc = $data['location'];
      $date = $data['date'];
      $body = 'Course booked details- <br /> Course Name- ' . $cname . ', <br /> Provider Name- ' . $pname . ', <br /> Location- ' . $loc . ', <br /> Date- ' . $date;
      $node = Node::create([
        'type' => 'tasks',
        'title' => $username . ' is book course for ' . $cname,
        'body' => [
          'value' => $body,
          'format' => 'basic_html',
        ],
        'field_date' => date('Y-m-d'),
        'field_task_status' => 'outstanding',
        'field_type' => 'notifications',
        'field_assigned_to' => 2,
      ]);
      if ($node->save()) {
        $options = [
          'uid' => \Drupal::currentUser()->id(),
          'cid' => $id,
          'course_name' => $cname,
          'provider_name' => $pname,
          'location' => $loc,
          'expiry_date' => $date,
        ];
        $query = \Drupal::database()->insert('booked_course')->fields($options)->execute();
        $options1 = [
          'uid' => \Drupal::currentUser()->id(),
          'cid' => $id,
          'course_name' => $cname,
        ];
        $query = \Drupal::database()->insert('notification')->fields($options1)->execute();
        $msg = ['message' => $this->t('Course Booked.')];
      }
      else {
        $msg = ['message' => $this->t('Booking not completed.')];
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
