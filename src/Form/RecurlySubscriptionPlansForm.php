<?php

/**
 * @file
 * Contains \Drupal\recurly\Form\RecurlySubscriptionPlansForm.
 */

namespace Drupal\recurly\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\recurly\Form\RecurlyFormBase;

/**
 * Recurly subscription plans form.
 */
class RecurlySubscriptionPlansForm extends RecurlyFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recurly_subscription_plans_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Initialize the Recurly client with the site-wide settings.
    if (!recurly_client_initialize()) {
      return ['#markup' => $this->t('Could not initialize the Recurly client.')];
    }
    try {
      $plans = recurly_subscription_plans();
    }
    catch (\Recurly_Error $e) {
      return $this->t('No plans could be retrieved from Recurly. Recurly reported the following error: "@error"', ['@error' => $e->getMessage()]);
    }
    $form['weights']['#tree'] = TRUE;

    $plan_options = [];
    $count = 0;
    foreach ($plans as $plan) {
      $plan_options[$plan->plan_code] = $plan->name;
      $form['#plans'][$plan->plan_code] = [
        'plan' => $plan,
        'unit_amounts' => [],
        'setup_amounts' => [],
      ];

      // TODO: Remove reset() calls once Recurly_CurrencyList implements
      // Iterator.
      // See https://github.com/recurly/recurly-client-php/issues/37
      $unit_amounts = in_array('IteratorAggregate', class_implements($plan->unit_amount_in_cents)) ? $plan->unit_amount_in_cents : reset($plan->unit_amount_in_cents);
      $setup_fees = in_array('IteratorAggregate', class_implements($plan->setup_fee_in_cents)) ? $plan->setup_fee_in_cents : reset($plan->setup_fee_in_cents);
      foreach ($unit_amounts as $unit_amount) {
        $form['#plans'][$plan->plan_code]['unit_amounts'][$unit_amount->currencyCode] = $this->t('@unit_price every @interval_length @interval_unit',
          [
            '@unit_price' => $this->recurlyFormatter->formatCurrency($unit_amount->amount_in_cents, $unit_amount->currencyCode),
            '@interval_length' => $plan->plan_interval_length,
            '@interval_unit' => $plan->plan_interval_unit,
          ]);
      }
      foreach ($setup_fees as $setup_fee) {
        $form['#plans'][$plan->plan_code]['setup_amounts'][$unit_amount->currencyCode] = $this->recurlyFormatter->formatCurrency($setup_fee->amount_in_cents, $setup_fee->currencyCode);
      }
      $form['weights'][$plan->plan_code] = [
        '#type' => 'hidden',
        '#default_value' => $count,
        '#attributes' => ['class' => ['weight']],
      ];
      $count++;
    }

    // Order our plans based on any existing value.
    $existing_plans = \Drupal::config('recurly.settings')->get('recurly_subscription_plans') ?: [];
    $plan_list = [];
    foreach ($existing_plans as $plan_code => $enabled) {
      if (isset($form['#plans'][$plan_code])) {
        $plan_list[$plan_code] = $form['#plans'][$plan_code];
      }
    }
    // Then add any new plans to the end.
    $plan_list += is_array($form['#plans']) ? $form['#plans'] : [];
    $form['#plans'] = $plan_list;

    foreach ($form['#plans'] as $plan_id => $details) {
      $operations = [];

      // Add an edit link if available for the current user.
      $operations['edit'] = [
        'title' => $this->t('edit'),
        'url' => $this->recurlyUrlManager->planEditUrl($details['plan']),
      ];

      // Add a purchase link if Hosted Payment Pages are enabled.
      if (\Drupal::moduleHandler()->moduleExists('recurly_hosted')) {
        $operations['purchase'] = [
          'title' => $this->t('purchase'),
          'url' => recurly_hosted_subscription_plan_purchase_url($details['plan']->plan_code),
        ];
      }

      $form['#plans'][$plan_id]['operations'] = [
        '#theme' => 'links',
        '#links' => $operations,
        '#attributes' => [
          'class' => ['links', 'inline'],
        ],
      ];
    }

    $header = [
      'plan_title' => ['data' => $this->t('Subscription plan'), 'colspan' => 1],
      'price' => $this->t('Price'),
      'setup_fee' => $this->t('Setup fee'),
      'trial' => $this->t('Trial'),
      'operations' => $this->t('Operations'),
    ];

    $options = [];
    foreach ($form['#plans'] as $plan_code => $plan_details) {
      $plan = $plan_details['plan'];

      $description = '';
      // Prepare the description string if one is given for the plan.
      if (!empty($plan->description)) {
        $description = $this->t('<div class="description">@description</div>', ['@description' => $plan->description]);
      }

      $form['recurly_subscription_plans'][$plan_code]['#title_display'] = 'none';
      $options[$plan_code] = [
        'plan_title' => $this->t('@planname <small>(@plancode)</small> @description', ['@planname' => $plan->name, '@plancode' => $plan_code, '@description' => $description]),
        'price' => implode('<br />', $plan_details['unit_amounts']),
        'setup_fee' => implode('<br />', $plan_details['setup_amounts']),
        'trial' => $plan->trial_interval_length ? $this->t('@trial_length @trial_unit', ['@trial_length' => $plan->trial_interval_length, '@trial_unit' => $plan->trial_interval_unit]) : $this->t('No trial'),
        'operations' => drupal_render($plan_details['operations']),
      ];
    }

    // @TODO: Implement draggable table.
    $form['recurly_subscription_plans'] = [
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
      '#empty' => $this->t('No subscription plans found. You can start by creating one in <a href=":url">your Recurly account</a>.', [':url' => \Drupal::config('recurly.settings')->get('recurly_subdomain') ? $this->recurlyUrlManager->hostedUrl('plans')->getUri() : 'http://app.recurly.com']),
      '#js_select' => FALSE,
      '#default_value' => $existing_plans,
      '#multiple' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update plans'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Order our variable based on the form order.
    $form_state_plans = $form_state->getValue('recurly_subscription_plans');
    $recurly_subscription_plans = [];
    foreach ($form_state->getUserInput()['weights'] as $plan_code => $weight) {
      if (isset($form_state_plans[$plan_code])) {
        $recurly_subscription_plans[$plan_code] = $form_state_plans[$plan_code];
      }
    }
    // Note that we don't actually need to care about the "weight" field values,
    // since the order of POST is actually changed based on the field position.
    \Drupal::configFactory()->getEditable('recurly.settings')->set('recurly_subscription_plans', $recurly_subscription_plans)->save();
    drupal_set_message($this->t('Status and order of subscription plans updated!'));
  }

}
