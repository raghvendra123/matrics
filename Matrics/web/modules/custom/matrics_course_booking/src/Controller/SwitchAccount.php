<?php

namespace Drupal\matrics_course_booking\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\taxonomy\Entity\Term;

/**
 * {@inheritdoc}
 */
class SwitchAccount extends ControllerBase {

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
  public function switchacc($tid) {
    $request = \Drupal::request();
    $name = $request->query->get('name');
    $session = $request->getSession();
    if ($name == 'All') {
      $session->set('tid', '');
      $message = 'You are now switched to All customer';
      \Drupal::Messenger()->addMessage($message);
      return new RedirectResponse('/switch-customer');
    }
    else {
      $session->set('tid', $tid);
      $get = $session->get('tid');
      if ($get != NULL) {
        $term_name = Term::load($get)->get('name')->value;
        $message = 'You are now switched to ' . $term_name;
      }
      else {
        $message = 'Session is not set, try again';
      }
      \Drupal::Messenger()->addMessage($message);
      return new RedirectResponse('/switch-customer');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function swich_customer() {
    $taxomonies = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('customer');
    $current_uid = \Drupal::currentUser()->id();
    $header = [
      'name' => t('Name'),
      'action' => t('Action'),
    ];
    $rows[] = [
      t('All'),
      Markup::create('<a href="/swich_account/all?name=All">Switch</a>'),
    ];
    foreach ($taxomonies as $term) {
      $tid = $term->tid;
      $query = \Drupal::database()->select('permissions_by_term_user', 't')->fields('t', ['uid'])->condition('t.tid', $tid);
      $result = $query->execute()->fetchAll();
      $uid = [];
      if (!empty($result)) {
        foreach ($result as $value) {
          $uid[] = $value->uid;
        }
      }
      if (in_array($current_uid, $uid)) {
        $rows[] = [
          $term->name,
          new FormattableMarkup('<a href="/swich_account/:tid?name=:name">Switch</a>',
            [
              ':tid' => $tid,
              ':name' => $term->name,
            ]),
        ];
      }
    }

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => ['max-age' => 0],
    ];
  }

}
