<?php

namespace Drupal\matrics_reports\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class ReportsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'matrics_reports';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['start_date'] = [
      '#type' => 'date',
      '#title' => t('Start Date'),
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => t('End Date'),
    ];

    $form['excel_file'] = [
      '#type' => 'checkboxes',
      '#title' => t('Data'),
      '#description' => t('Select any datatype'),
      '#options' => [
        'Training gap score', 'Training gap count', 'Certificates', 'Job Title', 'Location', 'Asset',
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Reports'),
      '#button_type' => 'primary',
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Start using PHP's built in file handler functions to create a temporary file.
    $handle = fopen('asset.csv', 'w');

    // Set up the header that will be displayed as the first line of the CSV file.
    // Blank strings are used for multi-cell values where there is a count of
    // the "keys" and a list of the keys with the count of their usage.
    $header = [
      'Nid',
      'Title',
    ];
    // Add the header as the first line of the CSV.
    fputcsv($handle, $header);
    // Find and load all of the Article nodes we are going to include.
    $connection = \Drupal::database();

    $query = \Drupal::database()->select('node_field_data', 'n');
    $query->fields('n', ['nid', 'title']);
    $query->condition('n.type', 'asset');
    $result = $query->execute()->fetchAll();

    // Iterate through the nodes.  We want one row in the CSV per Article.
    foreach ($result as $node) {
      // dump($node->nid); die;
      // Build the array for putting the row data together.
      $data['nid'] = $node->nid;
      $data['title'] = $node->title;

      // Add the data we exported to the next line of the CSV>.
      fputcsv($handle, array_values($data));
    }
    // Reset where we are in the CSV.
    rewind($handle);

    // Retrieve the data from the file handler.
    $csv_data = stream_get_contents($handle);

    // Close the file handler since we don't need it anymore.  We are not storing
    // this file anywhere in the filesystem.
    fclose($handle);

    // This is the "magic" part of the code.  Once the data is built, we can
    // return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "article-report.csv".
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="article-report.csv"');

    // This line physically adds the CSV data we created.
    $response->setContent($csv_data);
    $form_state->setResponse($response);

    return $response;
  }

}
