<?php

namespace Drupal\matrics_reports\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\matrics_reports\Controller\AssetList;
use Drupal\charts\Services\ChartsSettingsServiceInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Uuid\UuidInterface;

/**
 * Provides the form for adding countries.
 */
class AssetFilterForm extends FormBase {
  /**
   * The charts settings.
   *
   * @var \Drupal\charts\Services\ChartsSettingsServiceInterface
   */
  protected $chartSettings;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * Construct.
   *
   * @param \Drupal\charts\Services\ChartsSettingsServiceInterface $chartSettings
   *   The chartSettings.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Component\Uuid\UuidInterface $uuidService
   *   The uuidService.
   */
  public function __construct(ChartsSettingsServiceInterface $chartSettings, MessengerInterface $messenger, UuidInterface $uuidService) {

    $this->chartSettings = $chartSettings->getChartsSettings();
    $this->messenger = $messenger;
    $this->uuidService = $uuidService;
    $this->chartSettingsInterface = $chartSettings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('charts.settings'),
      $container->get('messenger'),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asset_listing_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $asset = \Drupal::request()->query->get('assets');
    $compliance_score = \Drupal::request()->query->get('compliance_score');
    $compliance_score_min = \Drupal::request()->query->get('compliance_score_min');
    $compliance_score_max = \Drupal::request()->query->get('compliance_score_max');

    $nids = \Drupal::entityQuery('node')->condition('type', 'asset')->execute();
    $nodes = Node::loadMultiple($nids);
    $assets[''] = $this->t('-Select Asset-');
    foreach ($nodes as $key => $value) {
      $title = $value->title->value;
      $nid = $value->nid->value;
      $assets[$nid] = $title;
    }
    $customerid = get_session_customerid();
    if ($customerid) {
      $assets = getAssetsOptions($customerid);
    }
    $form['asset_filter_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => 'row'],
    ];
    $form['asset_filter_container']['assets'] = [
      '#type' => 'select',
      '#title' => $this->t('Assets'),
      '#options' => $assets,
      '#maxlength' => 20,
      '#default_value' => isset($asset) ? [$asset] : '',
      '#prefix' => '<div class="col-md-3">',
      '#suffix' => '</div>',
    ];

    $form['asset_filter_container']['slider_title'] = [
      '#template' => '<p> </p><lable> {{ slider_title }} </lable>',
      '#type' => 'inline_template',
      '#context' => [
        'slider_title' => $this->t('Compliance Score'),
      ],
      '#prefix' => '<div class="col-lg-2 col-md-3 text-center">',
      '#suffix' => '</div>',
    ];

    $form['asset_filter_container']['slider_distance'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'slider-distance', 'class' => 'col-md-3'],
      '#title' => $this->t('Compliance Score'),
    ];
    $score_2 = "minRange()";
    $score_3 = "maxRange()";
    if (empty($compliance_score_min)) {
      $compliance_score_min = 1;
    }
    if (empty($compliance_score_max)) {
      $compliance_score_max = 60;
    }

    $form['asset_filter_container']['slider_distance']['slider_markup'] = [
      '#type' => 'inline_template',
      '#template' => '<div>
        <div inverse-left style="width:70%;"></div>
        <div inverse-right style="width:70%;"></div>
        <div range style="left:{{ compliance_score_min }}%;right:40%;"></div>
        <span thumb style="left:{{ compliance_score_min }}%;"></span>
        <span thumb style="left:{{ compliance_score_max }}%;"></span>
        <div sign style="left:{{ compliance_score_min }}%;">
          <span id="value">{{ compliance_score_min }}%</span>
        </div>
        <div sign style="left:{{ compliance_score_max }}%;">
          <span id="value">{{ compliance_score_max }}%</span>
        </div>
      </div>',
      '#context' => [
        'compliance_score_min' => $compliance_score_min,
        'compliance_score_max' => $compliance_score_max,
      ],
    ];

    $form['asset_filter_container']['slider_distance']['compliance_score_min'] = [
      '#type' => 'range',
      '#title' => $this->t('Compliance Score'),
      '#required' => FALSE,
      '#min' => 0,
      '#max' => 100,
      '#step' => 1,
      '#data-orientation' => 'horizontal',
      '#output' => 'below',
      '#output__field_prefix' => '',
      '#output__field_suffix' => '%',
      '#default_value' => isset($compliance_score_min) ? $compliance_score_min : '1',
      '#attributes' => ['tabindex' => 0, 'class' => []],
      '#tabindex' => 0,
      '#theme_wrappers' => [],
    ];

    $form['asset_filter_container']['slider_distance']['compliance_score_max'] = [
      '#type' => 'range',
      '#title' => $this->t('Compliance Score'),
      '#required' => FALSE,
      '#min' => 0,
      '#max' => 100,
      '#step' => 1,
      '#data-orientation' => 'horizontal',
      '#output' => 'below',
      '#output__field_prefix' => '',
      '#output__field_suffix' => '%',
      '#default_value' => isset($compliance_score_max) ? $compliance_score_max : '60',
      '#attributes' => ['tabindex' => 0],
      '#theme_wrappers' => [],
    ];

    $form['asset_filter_container']['actions'] = [
      '#type' => 'actions',
    ];

    $form['asset_filter_container']['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#default_value' => $this->t('Filter'),
      '#ajax' => [
        'callback' => '::assestsFilterResults',
        'wrapper' => 'asset-fieldset-container',
      ],
    ];
    $form['#attached']['library'][] = 'matrics_reports/matrics-range-slider';
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  function asset_filter_form_reset($form, &$form_state) {
    $form_state->setRebuild(FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function assestsFilterResults(array &$form, FormStateInterface $form_state) {
    $header = [
      'title' => t('Asset'),
      'content' => t('Asset Compliance Score'),
      'chart' => t('Chart'),
    ];
    $fields = $form_state->getValues();

    $statistics = new AssetList($this->chartSettingsInterface, $this->messenger, $this->uuidService);
    $rows = $statistics->getRowsResult($fields);
    $form['asset_fieldset_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'asset-fieldset-container'],
      '#weight' => 100,
    ];
    $form['asset_fieldset_container']['contain']['box']['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No content has been found.'),
    ];

    $form_state->setRebuild(TRUE);
    return $form['asset_fieldset_container'];

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $field = $form_state->getValues();
    $re_url = Url::fromRoute('matrics_reports.asset_list');

    $fields["assets"] = $field['assets'];
    $fields["compliance_score_min"] = $field['compliance_score_min'];
    $fields["compliance_score_max"] = $field['compliance_score_max'];
    $re_url->setOptions([
      'query' => [
        'assets' => $fields["assets"],
        'compliance_score_min' => $fields["compliance_score_min"],
        'compliance_score_max' => $fields["compliance_score_max"],
      ],
    ]);

    $form_state->setRedirectUrl($re_url);

  }

}
