<?php

namespace Drupal\matrics_schedule\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\user\Entity\User;

/**
 * Defines a confirmation form to confirm deletion of something by id.
 */
class SwapUserForm extends FormBase {

  /**
   * ID of the item to delete.
   *
   * @var int
   */
  public function getFormId() {
    return "confirm_assign_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uid = NULL, $id = NULL, $asset = NULL) {
    $this->id = $id;
    $this->uid = $uid;
    $this->asset = $asset;
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['my_actions'] = ['#type' => 'actions'];
    $form['my_actions']['cancel'] = [
      '#type' => 'button',
      '#value' => t('Cancel'),
      '#ajax' => [
        'callback' => [$this, 'cancelForm'],
        'event' => 'click',
        'progress' => FALSE,
      ],
    ];
    $user = User::load($this->uid);
    $uname = $user->get('name')->value;

    $form['my_actions']['contain'] = [
      '#type' => 'markup',
      '#markup' => 'Are you sure you want to assign job for <strong>' . $uname . '</strong> ?',
    ];
    $form['my_actions']['submit'] = [
      '#type' => 'button',
      '#value' => t('Confirm'),
      '#ajax' => [
        'callback' => [$this, 'submitForm'],
        'event' => 'click',
        'progress' => FALSE,
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = User::load($this->uid);
    $user->set('field_employee_status', "1");
    $user->save();

    $user = User::load($this->id);
    $user->set('field_employee_status', "2");
    $user->save();
    // $url = 'http://dev.matrics.agile451.net/scheduling';.
    $response = new AjaxResponse();
    // $response->addCommand(new CloseModalDialogCommand());.
    $service = \Drupal::service('matrics_schedule.reload_asset_job');
    $service = $service->sayHello($this->asset);

    // $response->addCommand(new ReplaceCommand('#box-container', ($service)));.
    $settings = ['my-setting' => 'setting'];

    $response->addCommand(new HtmlCommand('#box-contain', $service, $settings));
    $response->addCommand(new CloseModalDialogCommand());

    // $response->addCommand(new RedirectCommand($url));
    return $response;

  }

  /**
   * {@inheritdoc}
   */
  public function cancelForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
