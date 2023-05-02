<?php

namespace Drupal\matrics_dashboard\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Term;

/**
 * Class customer select form.
 */
class CustomerSelectForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'customer_select_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $vid = 'customer';
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $term_data['all'] = t('- All -');
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    $customer_id = get_session_customerid();
    $current_page = \Drupal::routeMatch()->getRouteName();

    $form['customer'] = [
      '#type' => 'select',
      '#title' => t('Switch Customer'),
      '#options' => $term_data,
      '#default_value' => $customer_id,
      '#ajax' => [
        'callback' => [$this, 'updateCustomerValue'],
        'wrapper' => '',
        'event' => 'change',
      ],
      '#prefix' => '<div class="customer-select">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function updateCustomerValue(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $request = \Drupal::request();
    $session = $request->getSession();
    $tid = $form_state->getValue('customer');
    if ($tid && $tid !== 'all') {
      $session->set('tid', $tid);
      $term_name = Term::load($tid)->get('name')->value;
      $message = 'You are now switched to ' . $term_name;
    }
    else {
      $session->set('tid', '');
      $message = 'Now you are switched to all customer.';
    }
    $form_state->setRebuild(TRUE);
    $url = Url::fromRoute('<current>');
    $command = new RedirectCommand($url->toString());
    $response->addCommand($command);

    \Drupal::Messenger()->addMessage($message);
    return $response;
    return $form['customer'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
