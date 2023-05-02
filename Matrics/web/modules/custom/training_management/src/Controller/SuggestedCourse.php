<?php

namespace Drupal\training_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Serialization\Json;
use Drupal\node\Entity\Node;

/**
 * An SuggestedCourse controller.
 */
class SuggestedCourse extends ControllerBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messanger service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuidService
   *   The uuid service.
   */
  public function __construct(MessengerInterface $messenger, UuidInterface $uuidService) {
    $this->messenger = $messenger;
    $this->uuidService = $uuidService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function suggested($id) {
    $current_user = \Drupal::currentUser()->id();
    // $uid = \Drupal::routeMatch()->getRawParameter('user');
    $query = \Drupal::database()->select('booked_course', 'b');
    $query->fields('b');
    $query->condition('b.uid', $current_user);

    $counts = $query->countQuery()->execute()->fetchField();
    if ($counts == 0) {
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
            $output[$index][1] = $course->title;
            $output[$index][2] = $provider_name->title;
            $output[$index][3] = $location;
            $output[$index][4] = $course_date;
            $output[$index][5] = $course_id;
            if (!empty($course_id)) {
              unset($courses[$course_id]);
            }
            $index++;
          }
          // print_r($rowkey);print_r($val);
          $rowkey++;
        }
      }
      foreach ($courses as $keys => $val) {
        if (!empty($keys)) {
          if (count($output) < 3) {
            $providers = get_providers_by_course($certificate, $keys);
            $course = \Drupal::database()->select('node_field_data', 'n')
              ->fields('n', ['title'])
              ->condition('n.nid', $keys)->execute()->fetch();
            $provider_id = array_key_last($providers);
            $provider_name = \Drupal::database()->select('node_field_data', 'n')
              ->fields('n', ['title'])
              ->condition('n.nid', $provider_id)->execute()->fetch();
            $output[$index][1] = $course->title;
            $output[$index][2] = $provider_name->title;
            $output[$index][3] = 'London';
            $output[$index][4] = date('d-m-Y');
            $output[$index][5] = $keys;
            unset($courses[$key]);
            $index++;
          }
        }
      }
    }
    else {
      $result = $query->execute()->fetchAll();
      foreach ($result as $key => $val) {
        $output[1] = $val->course_name;
        $output[2] = $val->provider_name;
        $output[3] = $val->location;
        $output[4] = $val->expiry_date;
      }
    }
    return [
      '#theme' => 'suggested_course',
      '#items' => $output,
      '#counts' => $counts,
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Inheritance.
   */
  public function suggestedCourseBooked($id) {
    $account = User::load(\Drupal::currentUser()->id());
    $username = $account->getDisplayName();
    $cname = \Drupal::request()->query->get('cname');
    $pname = \Drupal::request()->query->get('pname');
    $loc = \Drupal::request()->query->get('loc');
    $date = \Drupal::request()->query->get('date');
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
      'field_assigned_to' => \Drupal::currentUser()->id(),
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
      \Drupal::messenger()->addMessage(t('Course Booked.'), TRUE);
    }
    else {
      \Drupal::messenger()->addMessage(t('Booking not completed.'), TRUE);
    }
    $referer = \Drupal::request()->headers->get('referer');
    $response = new RedirectResponse($referer);
    return $response->send();
  }

}
