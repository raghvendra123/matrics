<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a Site Configuration Resource.
 *
 * @RestResource(
 *   id = "site_config",
 *   label = @Translation("Site Configuration Resource"),
 *   uri_paths = {
 *     "create" = "/matrics/site"
 *   }
 * )
 */
class SiteConfigurationResource extends ResourceBase {

  /**
   * A current user instance which is logged in the session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A logger instance.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   A currently logged user instance.
   */
  public function __construct(
    array $config,
    $module_id,
    $module_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct(
      $config,
      $module_id,
      $module_definition,
      $serializer_formats,
      $logger,
      $config_factory
    );
    $this->loggedUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
    if (!empty($data['image'])) {
      $image = $data['image'];
      $file = $this->entityTypeManager->getStorage('file')->load($image[0]);
      $file->setPermanent();
      $file->save();
    }

    $this->config('matrics_dashboard.settings')
      ->set('image', $data['image'])
      ->set('title', $data['title'])
      ->set('description', $data['description'])
      ->set('link', $data['link'])
      ->set('forgot_password', $data['forgot_password'])
      ->save();

    $response = ['message' => 'Updated Record Successfully'];
    $code = 200;
    return new ResourceResponse($response, $code);
  }

}
