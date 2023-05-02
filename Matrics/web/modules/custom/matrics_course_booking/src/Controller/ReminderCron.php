<?php

namespace Drupal\matrics_course_booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\HttpFoundation\Response;

/**
 * {@inheritdoc}
 */
class ReminderCron extends ControllerBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(Messenger $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public function reminder() {
    $query = \Drupal::entityQuery('user')->condition('roles', 'individual_employee');
    $uids = $query->execute();
    if (!empty($uids)) {
      foreach ($uids as $key => $uid) {
        $user = User::load($key);
        $course_paragraph_ids = $user->get('field_course_details')->getValue();

        if (!empty($course_paragraph_ids)) {
          foreach ($course_paragraph_ids as $key => $course_paragraph_id) {
            $course_paragrph_values = Paragraph::load($course_paragraph_id['target_id']);
            $start_date = !empty($course_paragrph_values->get('field_start_date')->getValue()[0]['value']) ? $course_paragrph_values->get('field_start_date')->getValue()[0]['value'] : NULL;
            $status = !empty($course_paragrph_values->get('field_status')->getValue()[0]['value']) ? $course_paragrph_values->get('field_status')->getValue()[0]['value'] : NULL;
            $close_booking = !empty($course_paragrph_values->get('field_close_booking')->getValue()[0]['value']) ? $course_paragrph_values->get('field_close_booking')->getValue()[0]['value'] : NULL;
            if ($status == 'completed' && $close_booking == 1) {
              $start_date = strtotime($start_date);
              $date = strtotime("+7 day", strtotime(date('Y-m-d')));
              if ($start_date != NULL && $start_date == $date) {
                $query = \Drupal::database()->select('notify_course', 'n')->fields('n', ['id'])->condition('n.user_id', $uid)->condition('n.pid', $course_paragraph_id['target_id'])->condition('n.type', 'reminder_course');
                $remind = $query->countQuery()->execute()->fetchField();
                if ($remind == 0) {
                  $result = \Drupal::database()->insert('notify_course')
                    ->fields([
                      'user_id' => $uid,
                      'pid' => $course_paragraph_id['target_id'],
                      'type' => 'reminder_course',
                    ])
                    ->execute();
                  if ($result) {
                    $mailManager = \Drupal::service('plugin.manager.mail');
                    $module = 'matrics_course_booking';
                    $key = 'reminder_email';
                    $to = $user->get('mail')->getValue()[0]['value'];
                    $params['message'] = 'Your course will start after 7 days.';
                    $params['title'] = 'Reminder Mail';
                    $langcode = $user->get('langcode')->getValue()[0]['value'];
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
                  }
                }
              }
            }
          }
        }
      }
    }
    return new Response(
      'Reminder send successfully',
       Response::HTTP_OK
    );
  }

}
