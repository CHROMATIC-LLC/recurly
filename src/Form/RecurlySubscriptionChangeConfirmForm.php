<?php

/**
 * @file
 * Contains \Drupal\recurly\Form\RecurlySubscriptionChangeConfirmForm.
 */

namespace Drupal\recurly\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Recurly subscription change form.
 */
class RecurlySubscriptionChangeConfirmForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recurly_subscription_change_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type = NULL, $entity = NULL, $subscription = NULL, $previous_plan = NULL, $new_plan = NULL) {
    // Note that currently Recurly does not have the ability to change the
    // subscription currency after it has started.
    // See http://docs.recurly.com/currencies
    $currency = $subscription->currency;
    $previous_amount = $previous_plan->unit_amount_in_cents[$currency]->amount_in_cents;
    $new_amount = $new_plan->unit_amount_in_cents[$currency]->amount_in_cents;

    // @TODO:
    // drupal_set_title() has been removed. There are now a few ways to set the
    // title dynamically, depending on the situation.
    //
    //
    // @see https://www.drupal.org/node/2067859
    // drupal_set_title(t('Confirm switch to @plan?', [
    //   '@plan' => $new_plan->name,
    // ]), FALSE);
    //
    $form['#entity_type'] = $entity_type;
    $form['#entity'] = $entity;
    $form['#subscription'] = $subscription;
    $form['#previous_plan'] = $previous_plan;
    $form['#new_plan'] = $new_plan;

    if ($new_amount >= $previous_amount) {
      $timeframe = \Drupal::config('recurly.settings')->get('recurly_subscription_upgrade_timeframe');
    }
    else {
      $timeframe = \Drupal::config('recurly.settings')->get('recurly_subscription_downgrade_timeframe');
    }
    $form['timeframe'] = [
      '#type' => 'radios',
      '#title' => t('Changes take effect'),
      '#options' => [
        'now' => t('Immediately'),
        'renewal' => t('On next renewal'),
      ],
      '#description' => t('If changes take effect immediately, the price difference will either result in a credit applied when the subscription renews or will trigger a prorated charge within the hour.'),
      '#default_value' => $timeframe,
      '#access' => \Drupal::currentUser()->hasPermission('administer recurly'),
    ];

    // TODO: We could potentially calculate the charge/credit amount here, but
    // math gets messy when switching between plans with different lengths.
    if ($timeframe === 'now') {
      $timeframe_message = '<p>' . t('The new plan will take effect immediately and a prorated charge (or credit) will be applied to this account.') . '</p>';
    }
    else {
      $timeframe_message = '<p>' . t('The new plan will take effect on the next billing cycle.') . '</p>';
    }
    $form['timeframe_help'] = [
      '#markup' => $timeframe_message,
      '#access' => !\Drupal::currentUser()->hasPermission('administer recurly'),
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Change plan'),
    ];

    // Add a cancel option to the confirmation form.
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#url' => \Drupal\Core\Url::fromRoute('recurly.subscription_change', ['entity' => $entity->id()]),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $form['#entity'];
    $entity_type = $form['#entity_type'];
    $subscription = $form['#subscription'];
    $new_plan = $form['#new_plan'];
    $timeframe = $form_state->getValue('timeframe');

    // Update the plan.
    $subscription->plan_code = $new_plan->plan_code;
    try {
      if ($timeframe === 'now') {
        $subscription->updateImmediately();
      }
      else {
        $subscription->updateAtRenewal();
      }
    }
    catch (Recurly_Error $e) {
      drupal_set_message(t('The plan could not be updated because the billing service encountered an error.'));
      return;
    }

    $message = t('Plan changed to @plan!', ['@plan' => $new_plan->name]);
    if ($timeframe !== 'now') {
      $message .= ' ' . t('Changes will become active starting <strong>@date</strong>.', ['@date' => recurly_format_date($subscription->current_period_ends_at)]);
    }
    drupal_set_message($message);
    $form_state->setRedirect('recurly.subscription_list', ['entity' => $entity->id()]);
  }

}