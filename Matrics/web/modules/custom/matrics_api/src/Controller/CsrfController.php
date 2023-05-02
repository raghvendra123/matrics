<?php

namespace Drupal\matrics_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\Messenger;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * {@inheritdoc}
 */
class CsrfController extends ControllerBase {

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
  public function validation() {

    $response['data'] = \Drupal::getContainer()->get('csrf_token')->validate($token, "viewuser/4");

    return new JsonResponse($response);
  }

}
