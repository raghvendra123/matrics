<?php

namespace Drupal\matrics_data_upload\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;

/**
 * Class ModalExample.
 *
 * @package Drupal\bifm\Form
 */
class TrainingMatrixForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'training_matrix_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $customerid = get_session_customerid();

    $form = [
      '#attributes' => ['enctype' => 'multipart/form-data'],
    ];

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => '<div class="filter-mobile-head training_matrix_filter"><h2>Filter</h2></div>',
      '#weight' => '-1',
    ];

    $validators = [
      'file_validate_extensions' => ['xlsx'],
    ];
    $option = get_assets();
    
    $form['asset'] = [
      '#type' => 'select',
      '#options' => $option,
      '#title' => 'Asset',
      '#required' => 'required',
      '#ajax' => [
        'callback' => '::assetDropdownCallback',
        'wrapper' => 'asset-fieldset-container',
      ],
      '#attributes' => ['class' => ['col-md-6']],
    ];

    if (empty($form_state->getValue('asset'))) {
      $selected_family = '';
    }
    else {
      $selected_family = $form_state->getValue('asset');
    }

    $form['asset_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asset-fieldset-container'],
    ];
    if (!empty($form_state->getValue('asset'))) {
      $form['asset_fieldset_container']['job_title'] = [
        '#type' => 'select',
        '#title' => 'Job Title',
        '#options' => get_jobtitle($selected_family),
        '#default_value' => ['all'],
        '#ajax' => [
          'callback' => '::training_matrix',
          'wrapper' => 'box-contain',
        ],
        '#attributes' => ['class' => ['col-md-6']],
        '#states' => [
          'visible' => [
              [':input[name="asset"]' => ['!value' => '']],
          ],
        ],
        '#executes_submit_callback' => FALSE,
      ];

      $form['asset_fieldset_container']['container']['contain'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'box-contain', 'class' => 'row'],
        '#weight' => '9',
      ];
      $form['asset_fieldset_container']['container']['contain']['box'] = [
        '#type' => 'markup',
        '#markup' => '',
        // '#attributes' => ['class' => 'col-md-6'],
        '#weight' => '2',
      ];
      // If (empty($form_state->getValue('job_title'))) {
      $table = $this->all_training_matrix($selected_family);
      $form['asset_fieldset_container']['container']['contain']['box']['table'] = [
        '#theme' => 'training_matrix',
        '#items' => $table,
        '#cache' => ['max-age' => 0],
      ];
      // }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function all_training_matrix($assests = '') {
    $customerid = get_session_customerid();
    $query1 = \Drupal::database()->select('node__field_asset', 'fa')
      ->fields('fa', ['entity_id'])
      ->condition('fa.field_asset_target_id', $assests)
      ->condition('fa.bundle', 'job_title');
    $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
    $query1->fields('n', ['title']);
    if (!empty($customerid)) {
      $query1->join('node__field_customer', 'cu', 'n.nid = cu.entity_id');
      $query1->condition('cu.field_customer_target_id', $customerid);
    }
    $job_title = $query1->execute()->fetchAll();

    foreach ($job_title as $job) {
      $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
        ->fields('jt', ['entity_id'])
        ->condition('jt.field_job_title_target_id', $job->entity_id);
      $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
      $query2->fields('u', ['name', 'uid']);
      $query2->join('user__roles', 'r', 'r.entity_id = u.uid');
      $query2->condition('r.roles_target_id', ['individual_employee'], "IN");
      if (!empty($customerid)) {
        $query2->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
        $query2->condition('cu.field_customer_target_id', $customerid);
      }
      $job_user = $query2->execute()->fetchAll();

      $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
      $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
      $query3->condition('fc.entity_id', $job->entity_id);
      $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
      $query3->fields('n', ['title']);
      $certi = $query3->execute()->fetchAll();

      $output[$assests][$job->entity_id]['job_title'] = $job->title;
      $certificate_name = [];
      $certificate_name['0'] = '';
      foreach ($certi as $cer) {
        $certificate_name[get_job_title_name($cer->field_certificate_target_id)] = get_job_title_name($cer->field_certificate_target_id);
      }
      foreach ($certi as $certificate) {
        $output[$assests][$job->entity_id]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
      }

      foreach ($job_user as $user) {
        $row = [];
        $certificate_list = '';

        $certificate_list = list_certificate($user->uid);

        $user_certificate = certificate_expiration_date($user->uid, 'all');
        $row[0] = $user->name;

        $row_key = 1;
        $row[$row_key]['date'] = '';
        $row[$row_key]['color'] = '';
        $row[$row_key]['url'] = '';
        foreach ($certificate_name as $certi) {
          if (!empty($certi)) {
            if (!empty($certificate_list) && in_array($certi, $certificate_list)) {
              $color = '';
              $field_expiry_date = isset($user_certificate[$certi]) ? strtotime($user_certificate[$certi]) : 0;
              $current_date = new DrupalDateTime();
              $current_date = strtotime($current_date);
              if ($field_expiry_date && $field_expiry_date > $current_date) {
                $diff = abs($field_expiry_date - $current_date);
                $years = floor($diff / (365 * 60 * 60 * 24));
                $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

                if ($months >= 6 || $years != 0) {
                  $color = '#C6EFCE';
                }
                elseif ($months <= 6 && $months >= 3) {
                  $color = '#FFCE33';
                }
                elseif ($months <= 3 && $months >= 1) {
                  $color = '#FFEB9C';
                }
                else {
                  $color = '#FFC7CE';
                }
                $title = '0';
                if (!empty($user_certificate[$certi])) {
                  $title = date('d-m-Y', strtotime($user_certificate[$certi]));
                }
                $row[$row_key]['date'] = $title;
                $row[$row_key]['color'] = $color;
                $row[$row_key]['url'] = $this->getCertificateDetail($user->uid);
              }
              else {
                $title = '0';
                if (!empty($user_certificate[$certi])) {
                  $title = date('d-m-Y', strtotime($user_certificate[$certi]));
                }
                $row[$row_key]['date'] = $title;
                $row[$row_key]['color'] = '#FFC7CE';
                $row[$row_key]['url'] = $this->getCertificateDetail($user->uid);
              }
              $row_key++;
            }
            else {
              $row[$row_key]['date'] = '0';
              $row[$row_key]['color'] = 'lightgray';
              $row[$row_key]['url'] = '#';
              $row_key++;
            }
          }
        }
        $output[$assests][$job->entity_id]['user'][$user->uid] = $row;
      }

    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getCertificateDetail($uid) {
    $url = Url::fromRoute('view.certificate_detail.page_1', ['arg_0' => $uid], [
      'absolute' => TRUE,
    ])->toString();
    return $url;
  }

  /**
   * Ajax callback for the color dropdown.
   */
  public function training_matrix(array $form, FormStateInterface $form_state) {
    $customerid = get_session_customerid();
    if ($form_state->getValue('job_title') == 'all') {
      $query1 = \Drupal::database()->select('node__field_asset', 'fa')
        ->fields('fa', ['entity_id'])
        ->condition('fa.field_asset_target_id', $form_state->getValue('asset'))
        ->condition('fa.bundle', 'job_title');
      $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
      $query1->fields('n', ['title']);
      if (!empty($customerid)) {
        $query1->join('node__field_customer', 'cu', 'n.nid = cu.entity_id');
        $query1->condition('cu.field_customer_target_id', $customerid);
      }
      $job_title = $query1->execute()->fetchAll();

      foreach ($job_title as $job) {
        $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
          ->fields('jt', ['entity_id'])
          ->condition('jt.field_job_title_target_id', $job->entity_id);
        $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
        $query2->fields('u', ['name', 'uid']);
        $query2->join('user__roles', 'r', 'r.entity_id = u.uid');
        $query2->condition('r.roles_target_id', ['individual_employee'], "IN");
        if (!empty($customerid)) {
          $query2->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
          $query2->condition('cu.field_customer_target_id', $customerid);
        }
        $job_user = $query2->execute()->fetchAll();

        $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
        $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
        $query3->condition('fc.entity_id', $job->entity_id);
        $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
        $query3->fields('n', ['title']);
        $certi = $query3->execute()->fetchAll();

        $output[$form_state->getValue('asset')][$job->entity_id]['job_title'] = $job->title;
        $certificate_name = [];
        $certificate_name['0'] = '';
        $certificate_paraid = [];
        foreach ($certi as $cer) {
          $certificate_name[get_job_title_name($cer->field_certificate_target_id)] = get_job_title_name($cer->field_certificate_target_id);
          $certificate_paraid[get_job_title_name($cer->field_certificate_target_id)] = $cer->field_certificate_target_id;
        }
        foreach ($certi as $certificate) {
          $output[$form_state->getValue('asset')][$job->entity_id]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
        }

        foreach ($job_user as $user) {
          $row = [];
          $certificate_list = '';

          $certificate_list = list_certificate($user->uid);

          $user_certificate = certificate_expiration_date_by_uid($user->uid);
          $row[0] = $user->name;
          // dump($user_certificate); die;.
          $row_key = 1;
          foreach ($certificate_name as $certi) {
            if (!empty($certi)) {
              if (in_array($certi, $certificate_list)) {
                $color = '';
                $field_expiry_date = strtotime($user_certificate[$certi]);
                $current_date = new DrupalDateTime();
                $current_date = strtotime($current_date);
                if ($field_expiry_date > $current_date) {
                  $diff = abs($field_expiry_date - $current_date);
                  $years = floor($diff / (365 * 60 * 60 * 24));
                  $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

                  if ($months >= 6 || $years != 0) {
                    $color = '#C6EFCE';
                  }
                  elseif ($months <= 6 && $months >= 3) {
                    $color = '#FFCE33';
                  }
                  elseif ($months <= 3 && $months >= 1) {
                    $color = '#FFEB9C';
                  }
                  else {
                    $color = '#FFC7CE';
                  }
                  $row[$row_key]['date'] = date('d-m-Y', strtotime($user_certificate[$certi]));
                  $row[$row_key]['color'] = $color;
                  $row[$row_key]['url'] = $this->getCertificateDetail($user->uid);
                }
                else {
                  $row[$row_key]['date'] = date('d-m-Y', strtotime($user_certificate[$certi]));
                  $row[$row_key]['color'] = '#FFC7CE';
                  $row[$row_key]['url'] = $this->getCertificateDetail($user->uid);
                }
                $row_key++;
              }
              else {
                $row[$row_key]['date'] = '0';
                $row[$row_key]['color'] = 'lightgray';
                $row[$row_key]['url'] = '#';
                $row_key++;
              }
            }
          }
          $output[$form_state->getValue('asset')][$job->entity_id]['user'][$user->uid] = $row;
        }
      }
    }
    else {
      $query2 = \Drupal::database()->select('user__field_job_title', 'jt')
        ->fields('jt', ['entity_id'])
        ->condition('jt.field_job_title_target_id', $form_state->getValue('job_title'));
      $query2->join('users_field_data', 'u', 'u.uid = jt.entity_id');
      $query2->fields('u', ['name', 'uid']);
      $query2->join('user__roles', 'r', 'r.entity_id = u.uid');
      $query2->condition('r.roles_target_id', ['individual_employee'], "IN");
      if (!empty($customerid)) {
        $query2->join('user__field_customer', 'cu', 'u.uid = cu.entity_id');
        $query2->condition('cu.field_customer_target_id', $customerid);
      }
      $job_user = $query2->execute()->fetchAll();

      $query3 = \Drupal::database()->select('node__field_certificate', 'fc');
      $query3->fields('fc', ['entity_id', 'field_certificate_target_id']);
      $query3->condition('fc.entity_id', $form_state->getValue('job_title'));
      $query3->join('node_field_data', 'n', 'n.nid = fc.field_certificate_target_id');
      $query3->fields('n', ['title']);
      $certi = $query3->execute()->fetchAll();

      $output[$form_state->getValue('asset')][$form_state->getValue('job_title')]['job_title'] = get_job_title_name($form_state->getValue('job_title'));
      $certificate_name = [];
      $certificate_name['0'] = '';
      foreach ($certi as $cer) {
        $certificate_name[get_job_title_name($cer->field_certificate_target_id)] = get_job_title_name($cer->field_certificate_target_id);
      }
      foreach ($certi as $certificate) {
        $output[$form_state->getValue('asset')][$form_state->getValue('job_title')]['certificate'][$certificate->field_certificate_target_id] = $certificate->title;
      }

      foreach ($job_user as $user) {
        $row = [];
        $certificate_list = '';

        $certificate_list = list_certificate($user->uid);

        $user_certificate = certificate_expiration_date_by_uid($user->uid);
        $row[0] = $user->name;
        // dump($user_certificate); die;.
        $row_key = 1;
        foreach ($certificate_name as $certi) {
          if (!empty($certi)) {
            if (in_array($certi, $certificate_list)) {
              $color = '';
              $field_expiry_date = strtotime($user_certificate[$certi]);
              $current_date = new DrupalDateTime();
              $current_date = strtotime($current_date);

              if ($field_expiry_date > $current_date) {
                $diff = abs($field_expiry_date - $current_date);
                $years = floor($diff / (365 * 60 * 60 * 24));
                $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

                if ($months >= 6 || $years != 0) {
                  $color = '#C6EFCE';
                }
                elseif ($months <= 6 && $months >= 3) {
                  $color = '#FFCE33';
                }
                elseif ($months <= 3 && $months >= 1) {
                  $color = '#FFEB9C';
                }
                else {
                  $color = '#FFC7CE';
                }
                $row[$row_key]['date'] = date('d-m-Y', strtotime($user_certificate[$certi]));
                $row[$row_key]['color'] = $color;
                $row[$row_key]['url'] = $this->getCertificateDetail($user->uid);
              }
              else {
                $row[$row_key]['date'] = date('d-m-Y', strtotime($user_certificate[$certi]));
                $row[$row_key]['color'] = '#FFC7CE';
                $row[$row_key]['url'] = $this->getCertificateDetail($user->uid);
              }
              $row_key++;
            }
            else {
              $row[$row_key]['date'] = '0';
              $row[$row_key]['color'] = 'lightgray';
              $row[$row_key]['url'] = '#';
              $row_key++;
            }
          }
        }
        $output[$form_state->getValue('asset')][$form_state->getValue('job_title')]['user'][$user->uid] = $row;
      }

      if (empty($row)) {
        $output['data'] = 'There is no Employee related to selected Asset and Job Title. Please try with other Job Title or Asset.';
      }
    }

    $element = $form['asset_fieldset_container']['container'];
    $element['contain']['box']['table'] = [
      '#theme' => 'training_matrix',
      '#items' => $output,
      '#cache' => ['max-age' => 0],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('excel_file') == NULL) {
      $form_state->setErrorByName('excel_file', $this->t('upload proper File'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function assetDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['asset_fieldset_container'];
  }

  /**
   * Helper function to populate the second dropdown.
   *
   * This would normally be pulling data from the database.
   *
   * @param string $key
   *   This will determine which set of options is returned.
   *
   * @return array
   *   Dropdown options
   */
  public static function getSecondDropdownOptions($key = '') {
    $options = [];
    if (!empty($key)) {
      $options[''] = '- select -';
      $options['all'] = 'Select all';
      $query1 = \Drupal::database()->select('node__field_asset', 'fa')
        ->fields('fa', ['entity_id'])
        ->condition('fa.field_asset_target_id', $key)
        ->condition('fa.bundle', 'job_title');
      $query1->join('node_field_data', 'n', 'n.nid = fa.entity_id');
      $query1->fields('n', ['title']);
      $job_title = $query1->execute()->fetchAll();

      foreach ($job_title as $job) {
        $options[$job->entity_id] = $job->title;
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = \Drupal::entityTypeManager()->getStorage('file')
      ->load($form_state->getValue('excel_file')[0]);
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
    $certificate = $rows[0];
    unset($certificate[0]);
    $asset = $form_state->getValue('asset');

    $i = 1;
    foreach ($certificate as $key => $cert) {
      $query1 = \Drupal::database()->select('node_field_data', 'n');
      $query1->fields('n', ['nid', 'title']);
      $query1->condition('n.title', strtolower($cert));
      $cert_id = $query1->execute()->fetchAll();
      $certificate_id[$i] = $cert_id[0]->nid;
      $i++;
    }

    unset($rows[0]);
    foreach ($rows as $row) {
      $job_title = $row[0];
      unset($row[0]);
      $certificate_select = [];
      foreach ($row as $k => $cert_status) {
        if ($cert_status == 1) {
          $certificate_select[] = $certificate_id[$k];
        }

      }

      $values = \Drupal::entityQuery('node')->condition('title', $job_title)->condition('field_asset', $form_state->getValue('asset'))->execute();
      $node_not_exists = empty($values);

      if (!empty($job_title)) {
        if ($node_not_exists) {
          /*if node does not exist create new node*/
          $node = \Drupal::entityTypeManager()->getStorage('node')->create([
            // ===here asset is the content type mechine name
            'type' => 'job_title',
            'title'      => $job_title,
            'field_asset' => $form_state->getValue('asset'),
            'field_certificate' => $certificate_select,
          ]);
          $node->save();
        }
        else {
          /*if node exist update the node*/
          $nid = reset($values);

          $node = Node::load($nid);
          $node->setTitle($job_title);
          $node->set('field_asset', $form_state->getValue('asset'));
          $node->set('field_certificate', $certificate_select);
          $node->save();
        }
      }
    }
    \Drupal::messenger()->addMessage('imported successfully');
  }

}
