<?php

namespace Drupal\training_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * An closeBooking controller.
 */
class CloseBooking extends ControllerBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messanger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function content($id, $uid, $cid, $status) {
    $certificates = [];
    if ($status == 'completed') {
      $user = User::load($uid);
      $certificates = $user->get('field_certificates')->getValue();
      foreach ($certificates as $key => $certificate) {
        $c_id = (int) $certificate['target_id'];
        $c = Paragraph::load($c_id);
        $enable = $c->get('field_enable')->getValue();
        if ($enable == 1) {
          if ($c_id == $cid) {
            unset($certificates[$key]);
          }
        }
        else {
          unset($certificates[$key]);
        }
      }
    }
    else {
      $certificates = [0];
    }

    if (!empty($certificates)) {
      $pid = $id;
      $p = Paragraph::load($pid);
      $p->set('field_close_booking', '1');
      if ($p->save()) {
        \Drupal::messenger()->addMessage($this->t('Booking is closed successfully'), 'status', TRUE);
      }
      else {
        \Drupal::messenger()->addMessage($this->t('Course not updated, please try again'), 'error', TRUE);
      }
    }
    else {
      \Drupal::messenger()->addMessage($this->t('No certificate uploaded. Please add certificate of expiry date is greater than 6 months.'), 'error', TRUE);
    }
    $response = new RedirectResponse('/training-management');
    $response->send();
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function contentDelete($id) {
    $pid = $id;
    $p = Paragraph::load($pid);
    $p->delete();
    \Drupal::messenger()->addMessage($this->t('Booking is closed successfully'), 'status', TRUE);
    $response = new RedirectResponse('/training-management');
    $response->send();
    return $response;
  }

}
