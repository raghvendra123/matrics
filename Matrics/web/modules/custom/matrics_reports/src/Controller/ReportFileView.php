<?php

namespace Drupal\matrics_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

/**
 * An report controller.
 */
class ReportFileView extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function view($fid = NULL) {
    if ($fid) {
      $file = File::load($fid);
      if ($file) {
        $fileurl = $file->createFileUrl();
        $uri = $file->getFileUri();
        $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
        $file_path = $stream_wrapper_manager->realpath();
        $fronturl = Url::fromRoute('<front>')->setAbsolute()->toString();
        $absolute_path = $fronturl . "" . $fileurl;
        $csvFile = fopen($uri, 'r');
        $customer = [];
        while (!feof($csvFile)) {
          $customer[] = fgetcsv($csvFile);
        }
        $rows = [];
        if (isset($customer[0])) {
          $headers = $customer[0];
          foreach ($customer as $keys => $values) {
            if ($keys > 0) {
              $single_value = [];
              foreach ($values as $key => $value) {
                $single_value[] = Markup::create($value);
              }  
              $rows[] = $single_value;
            }
          }
        }

        $url = Url::fromUri($absolute_path);
        $link_options = [
          'attributes' => [
            'target' => '_blank',
            'class' => 'btn btn-primary',
          ],
        ];
        $url->setOptions($link_options);
        $link = Link::fromTextAndUrl(t('Download report'), $url);
        return [
          '#theme' => 'table',
          '#rows' => $rows,
          '#header' => $headers,
          '#empty' => 'No records',
          '#caption' => $link->toRenderable(),
        ];
      }
    }
  }

}
