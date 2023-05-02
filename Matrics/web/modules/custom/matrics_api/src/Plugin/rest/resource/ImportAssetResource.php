<?php

namespace Drupal\matrics_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a Asset Import Resource.
 *
 * @RestResource(
 *   id = "asset_import",
 *   label = @Translation("Asset Import"),
 *   uri_paths = {
 *     "create" = "/import/asset"
 *   }
 * )
 */
class ImportAssetResource extends ResourceBase {

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
  public function post($data) {
    $file = \Drupal::entityTypeManager()->getStorage('file')
      ->load($data['excel_file'][0]);
    $full_path = $file->get('uri')->value;
    $file_name = basename($full_path);
    $inputFileName = \Drupal::service('file_system')->realpath('public://' . $file_name);
    $spreadsheet = IOFactory::load($inputFileName);
    $sheetData = $spreadsheet->getActiveSheet();
    $rows = [];
    foreach ($sheetData->getRowIterator() as $row) {
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(FALSE);
      $cells = [];
      foreach ($cellIterator as $cell) {
        $cells[] = $cell->getValue();
      }
      $rows[] = $cells;
    }
    unset($rows[0]);
    foreach ($rows as $row) {
      $values = \Drupal::entityQuery('node')->condition('title', $row[0])->execute();
      $node_not_exists = empty($values);
      if ($node_not_exists) {
        /*if node does not exist create new node*/
        $node = \Drupal::entityTypeManager()->getStorage('node')->create([
          'type' => 'asset',
          'title' => $row[0],
          'field_company' => $row[1],
          'field_location' => $row[2],
          'field_status' => $row[3],
          'field_number_of_employee' => $row[4],
          'field_training_gap_score' => $row[5],
        ]);
        $node->save();
      }
      else {
        /*if node exist update the node*/
        $nid = reset($values);
        $node = Node::load($nid);
        $node->setTitle($row[0]);
        $node->set("field_company", $row[1]);
        $node->set("field_location", $row[2]);
        $node->set("field_status", $row[3]);
        $node->set("field_number_of_employee", $row[4]);
        $node->set("field_training_gap_score", $row[5]);
        $node->save();
      }
    }

    $response = ['message' => 'imported successfully'];
    $code = 200;
    return new ResourceResponse($response, $code);
  }

}
