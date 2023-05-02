<?php

namespace Drupal\matrics_reports\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\matrics_reports\Controller\AssetCompliance;

/**
 * Provides the form for adding countries.
 */
class ComplianceFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asset_compliance_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $asset_data = []) {
    $assetcompliance = new AssetCompliance();
    $results = $assetcompliance->getComplianceFilterResult($asset_data);

    $rows = $results['rows'];
    $total_compliance = $results['total_compliance'];
    $asset_compliance = $results['asset_compliance'];
    $total_emp = $results['total_emp'];

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'box-container', 'class' => 'row'],
      '#weight' => '9',
    ];
    $form['container']['asset'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Asset'),
      '#default_value' => $asset_data['asset'],
    ];
    $form['container']['expiry_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Expiry date'),
      '#prefix' => '<div class="col-md-3">',
      '#suffix' => '</div>',
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#default_value' => $this->t('Filter'),
      '#ajax' => [
        'callback' => '::filterAssestCertifcate',
        'wrapper' => 'asset-fieldset-container',
      ],
    ];

    $row_total[] = [$total_emp, $asset_compliance . '%', ""];

    $header_total = [
      'title' => t('Number of employee'),
      'content' => t('Asset Compliance Score'),
      'status' => '',
    ];
    $form['asset_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asset-fieldset-container'],
      '#weight' => 100,
    ];
    $form['asset_fieldset_container']['contain']['box']['table'] = [
      '#type' => 'table',
      '#header' => $header_total,
      '#rows' => $row_total,
      '#empty' => t('No content has been found.'),
      '#cache' => ['max-age' => 0],
    ];

    $header = [
      'title' => t('Employee'),
      'content' => t('Employee Compliance Score'),
      'status' => t('Employee Status'),
    ];

    $form['asset_fieldset_container']['contain']['box']['table2'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No content has been found.'),
      '#cache' => ['max-age' => 0],
    ];
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function filterAssestCertifcate(array &$form, FormStateInterface $form_state) {
    $fields = $form_state->getValues();
    $statistics = new AssetCompliance();
    $asset_data = $statistics->getComplianceFilterResult($fields);
    $rows = $asset_data['rows'];
    $total_compliance = $asset_data['total_compliance'];
    $asset_compliance = $asset_data['asset_compliance'];
    $total_emp = $asset_data['total_emp'];
    $row_total[] = [$total_emp, $asset_compliance . '%', ""];
    $header_total = [
      'title' => t('Number of employee'),
      'content' => t('Asset Compliance Score'),
      'status' => '',
    ];
    $form['asset_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asset-fieldset-container'],
      '#weight' => 100,
    ];
    $form['asset_fieldset_container']['contain']['box']['table'] = [
      '#type' => 'table',
      '#header' => $header_total,
      '#rows' => $row_total,
      '#empty' => t('No content has been found.'),
      '#cache' => ['max-age' => 0],
    ];
    $header = [
      'title' => t('Employee'),
      'content' => t('Employee Compliance Score'),
      'status' => t('Employee Status'),
    ];
    $form['asset_fieldset_container']['contain']['box']['table2'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No content has been found.'),
      '#cache' => ['max-age' => 0],
    ];

    $form_state->setRebuild(TRUE);
    return $form['asset_fieldset_container'];

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $field = $form_state->getValues();
    $fields["asset"] = $field['asset'];
    $re_url = Url::fromRoute('matrics_reports.asset_compliance', ['asset' => $fields["asset"]]);

    $fields["expiry_date"] = $field['expiry_date'];
    $re_url->setOptions([
      'query' => [
        'asset' => $fields["asset"],
        'expiry_date' => $fields["expiry_date"],
      ],
    ]);
    $form_state->setRedirectUrl($re_url);
  }

}
