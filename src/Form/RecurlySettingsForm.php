<?php

/**
 * @file
 * Contains \Drupal\recurly\Form\RecurlySettingsForm.
 */

namespace Drupal\recurly\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class RecurlySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recurly_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('recurly.settings')
      ->set('recurly_private_api_key', $form_state->getValue('recurly_private_api_key'))
      ->set('recurly_public_key', $form_state->getValue('recurly_public_key'))
      ->set('recurly_subdomain', $form_state->getValue('recurly_subdomain'))
      ->set('recurly_default_currency', $form_state->getValue('recurly_default_currency'))
      ->set('recurly_listener_key', $form_state->getValue('recurly_listener_key'))
      ->set('recurly_push_logging', $form_state->getValue('recurly_push_logging'))
      ->set('recurly_entity_type', $form_state->getValue('recurly_entity_type'))
      ->set('recurly_token_mapping', $form_state->getValue('recurly_token_mapping'))
      ->set('recurly_pages', $form_state->getValue('recurly_pages'))
      ->set('recurly_coupon_page', $form_state->getValue('recurly_coupon_page'))
      ->set('recurly_subscription_display', $form_state->getValue('recurly_subscription_display'))
      ->set('recurly_subscription_max', $form_state->getValue('recurly_subscription_max'))
      ->set('recurly_subscription_upgrade_timeframe', $form_state->getValue('recurly_subscription_upgrade_timeframe'))
      ->set('recurly_subscription_downgrade_timeframe', $form_state->getValue('recurly_subscription_downgrade_timeframe'))
      ->set('recurly_subscription_cancel_behavior', $form_state->getValue('recurly_subscription_cancel_behavior'))
      ->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['recurly.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Recommend setting some subscription plans if not enabled.
    $plan_options = \Drupal::config('recurly.settings')->get('recurly_subscription_plans') ?: array();
    if (empty($plan_options) && \Drupal::config('recurly.settings')->get('recurly_private_api_key') && \Drupal::config('recurly.settings')->get('recurly_pages')) {
      drupal_set_message(t('Recurly built-in pages are enabled, but no plans have yet been enabled. Enable plans on the <a href="!url">Subscription Plans page</a>.', array('!url' => \Drupal\Core\Url::fromRoute('recurly.subscription_plans_overview'))), 'warning', FALSE);
    }

    // Add form elements to collect default account information.
    $form['account'] = [
      '#type' => 'details',
      '#title' => t('Default account settings'),
      '#description' => t('Configure this information based on the "API Credentials" section within the Recurly administration interface.'),
      '#open' => TRUE,
    ];
    $form['account']['recurly_private_api_key'] = [
      '#type' => 'textfield',
      '#title' => t('Private API Key'),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_private_api_key'),
    ];
    $form['account']['recurly_public_key'] = [
      '#type' => 'textfield',
      '#title' => t('Public Key'),
      '#description' => t('Enter this if needed for Recurly.js. Note that this version of the Recurly module only supports Recurly.js v3, which uses the "public key" and not the "transparent post key" used by Recurly.js v2.'),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_public_key'),
    ];
    $form['account']['recurly_subdomain'] = [
      '#type' => 'textfield',
      '#title' => t('Subdomain'),
      '#description' => t("The subdomain of your account."),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_subdomain'),
    ];
    $form['account']['recurly_default_currency'] = [
      '#type' => 'textfield',
      '#title' => t('Default currency'),
      '#description' => t('Enter the 3-character currency code for the currency you would like to use by default. You can find a list of supported currencies in your <a href="!url">Recurly account currencies page</a>.', [
        '!url' => recurly_hosted_url('configuration/currencies')
        ]),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_default_currency'),
      '#size' => 3,
      '#maxlength' => 3,
    ];

    // Add form elements to configure default push notification settings.
    $form['push'] = [
      '#type' => 'details',
      '#title' => t('Push notification settings'),
      '#description' => t('If you have supplied an HTTP authentication username and password in your Push Notifications settings at Recurly, your web server must be configured to validate these credentials at your listener URL.'),
      '#open' => TRUE,
    ];
    $form['push']['recurly_listener_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Listener URL key'),
      '#description' => t('Customizing the listener URL gives you protection against fraudulent push notifications.') . '<br />' . t('Based on your current key, you should set @url as your Push Notification URL at Recurly.',
        array(
          // @FIXME
          // '@url' => \Drupal\Core\Url::fromUri('recurly/listener/' . \Drupal::config('recurly.settings')->get('recurly_listener_key'), array('absolute' => TRUE)),
        )),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_default_currency') ?: '',
      '#required' => TRUE,
      '#size' => 32,
      // @FIXME
      // '#field_prefix' => url('recurly/listener/', array('absolute' => TRUE)),
    );

    $form['push']['recurly_push_logging'] = [
      '#type' => 'checkbox',
      '#title' => t('Log authenticated incoming push notifications. (Primarily used for debugging purposes.)'),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_push_logging'),
    ];

    $entity_types = \Drupal::entityManager()->getDefinitions();
    $entity_options = [];
    foreach ($entity_types as $entity_name => $entity_info) {
      $entity_options[$entity_name] = $entity_info->getLabel()->render();
      // @FIXME:
      // $first_bundle_name = key($entity_info['bundles']);
    //   // Don't generate a list of bundles if this entity does not have types.
    //   if ($entity_info['bundles'] > 1 && $first_bundle_name !== $entity_name) {
    //     foreach ($entity_info['bundles'] as $bundle_name => $bundle_info) {
    //       $entity_type_options[$entity_name][$bundle_name] = $bundle_info['label'];
    //     }
    //   }
    }

    // If any of the below options change we need to rebuild the menu system.
    // Keep a record of their current values.
    $recurly_entity_type = \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: 'user';
    $pages_previous_values = array(
      'recurly_entity_type' => $recurly_entity_type,
      'recurly_bundle_' . $recurly_entity_type => \Drupal::config('recurly.settings')->get('recurly_bundle_' . $recurly_entity_type),
      'recurly_pages' => \Drupal::config('recurly.settings')->get('recurly_pages') ?: 1,
      'recurly_coupon_page' => \Drupal::config('recurly.settings')->get('recurly_coupon_page') ?: 1,
      'recurly_subscription_plans' => \Drupal::config('recurly.settings')->get('recurly_subscription_plans') ?: array(),
      'recurly_subscription_max' => \Drupal::config('recurly.settings')->get('recurly_subscription_max') ?: 1,
    );
    $form_state->setValue('pages_previous_values', $pages_previous_values);


    $form['sync'] = [
      '#type' => 'details',
      '#title' => t('Recurly account syncing'),
      '#open' => !empty($recurly_entity_type),
      '#description' => t("Each time a particular object type (User, Node, Group, etc.) is updated, you may have information sent to Recurly to keep the contact information kept up-to-date. It is extremely important to maintain updated contact information in Recurly, as when an account enters the dunning process, the e-mail account in Recurly is the primary contact address."),
    ];
    $form['sync']['recurly_entity_type'] = [
      '#title' => t('Send Recurly account updates for each'),
      '#type' => 'select',
      '#options' => [
        '' => 'Nothing (disabled)'
        ] + $entity_options,
      '#default_value' => $recurly_entity_type,
    ];
    if (!empty($entity_type_options)) {
      foreach ($entity_type_options as $entity_name => $bundles) {
        $form['sync']['recurly_bundles']['recurly_bundle_' . $entity_name] = array(
          '#title' => t('Specifically the following @entity type', array('@entity' => $entity_types[$entity_name]['label'])),
          '#type' => 'select',
          '#options' => $bundles,
          '#default_value' => \Drupal::config('recurly.settings')->get('recurly_bundle_' . $entity_name),
          '#states' => array(
            'visible' => array(
              'select[name="recurly_entity_type"]' => array('value' => $entity_name),
            ),
          ),
        );
      }
    }

    $mapping = recurly_token_mapping();
    $form['sync']['recurly_token_mapping'] = [
      '#title' => t('Token mappings'),
      '#type' => 'details',
      '#open' => FALSE,
      '#tree' => TRUE,
      '#parents' => [
        'recurly_token_mapping'
        ],
      '#description' => t('Each Recurly account field is displayed below, specify a token that will be used to update the Recurly account each time the object (node or user) is updated. The Recurly "username" field is automatically populated with the object name (for users) or title (for nodes).'),
    ];
    $form['sync']['recurly_token_mapping']['email'] = [
      '#title' => t('Email'),
      '#type' => 'textfield',
      '#default_value' => $mapping['email'],
      '#description' => t('i.e. [user:mail] or [node:author:mail]'),
    ];
    $form['sync']['recurly_token_mapping']['username'] = [
      '#title' => t('Username'),
      '#type' => 'textfield',
      '#default_value' => $mapping['username'],
      '#description' => t('i.e. [user:name] or [node:title]'),
    ];
    $form['sync']['recurly_token_mapping']['first_name'] = [
      '#title' => t('First name'),
      '#type' => 'textfield',
      '#default_value' => $mapping['first_name'],
    ];
    $form['sync']['recurly_token_mapping']['last_name'] = [
      '#title' => t('Last name'),
      '#type' => 'textfield',
      '#default_value' => $mapping['last_name'],
    ];
    $form['sync']['recurly_token_mapping']['company_name'] = [
      '#title' => t('Company'),
      '#type' => 'textfield',
      '#default_value' => $mapping['company_name'],
    ];
    $form['sync']['recurly_token_mapping']['address1'] = [
      '#title' => t('Address line 1'),
      '#type' => 'textfield',
      '#default_value' => $mapping['address1'],
    ];
    $form['sync']['recurly_token_mapping']['address2'] = [
      '#title' => t('Address line 2'),
      '#type' => 'textfield',
      '#default_value' => $mapping['address2'],
    ];
    $form['sync']['recurly_token_mapping']['city'] = [
      '#title' => t('City'),
      '#type' => 'textfield',
      '#default_value' => $mapping['city'],
    ];
    $form['sync']['recurly_token_mapping']['state'] = [
      '#title' => t('State'),
      '#type' => 'textfield',
      '#default_value' => $mapping['state'],
      '#description' => t('Values sent to Recurly must be two-letter abbreviations.'),
    ];
    $form['sync']['recurly_token_mapping']['zip'] = [
      '#title' => t('Zip code'),
      '#type' => 'textfield',
      '#default_value' => $mapping['zip'],
    ];
    $form['sync']['recurly_token_mapping']['country'] = [
      '#title' => t('Country'),
      '#type' => 'textfield',
      '#default_value' => $mapping['country'],
      '#description' => t('Values sent to Recurly must be two-letter abbreviations.'),
    ];
    $form['sync']['recurly_token_mapping']['phone'] = [
      '#title' => t('Phone number'),
      '#type' => 'textfield',
      '#default_value' => $mapping['phone'],
    ];
    $form['sync']['recurly_token_mapping']['help'] = [
      '#theme' => 'token_tree',
      '#token_types' => array_keys($entity_options),
      '#global_types' => FALSE,
    ];
    $form['pages'] = [
      '#type' => 'details',
      '#title' => t('Built-in subscription/invoice pages'),
      '#open' => \Drupal::config('recurly.settings')->get('recurly_pages'),
      '#description' => t('The Recurly module provides built-in pages for letting users view their own recent invoices on the site instead of needing to go to the Recurly site. If a companion module is enabled such as the Recurly Hosted Pages or Recurly.js module (both included with this project), appropriate links to update billing information or subscribe will also be displayed on these pages.'),
      '#states' => [
        'visible' => [
          'select[name="recurly_entity_type"]' => [
            '!value' => ''
            ]
          ]
        ],
    ];
    $form['pages']['recurly_pages'] = [
      '#title' => t('Enable built-in pages'),
      '#type' => 'checkbox',
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_pages'),
      '#description' => t('Hosted pages will be enabled on the same object type as the "Account Syncing" option above.'),
    ];

    // All the settings below are dependent upon the pages option being enabled.
    $pages_enabled = [
      'visible' => [
        'input[name=recurly_pages]' => [
          'checked' => TRUE
          ]
        ]
      ];
    $form['pages']['recurly_coupon_page'] = [
      '#title' => t('Enable coupon redemption page'),
      '#type' => 'checkbox',
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_coupon_page') ?: 1,
      '#description' => t('Show the "Redeem coupon" tab underneath the subscription page.'),
      '#states' => $pages_enabled,
    ];
    $form['pages']['recurly_subscription_display'] = [
      '#title' => t('List subscriptions'),
      '#type' => 'radios',
      // @TODO: Convert these array keys to class constants.
      '#options' => [
        'live' => t('Live subscriptions (active, trials, canceled, and past due)'),
        'all' => t('All (includes expired subscriptions)'),
      ],
      '#description' => t('Users may subscribe or switch between any of the enabled plans by visiting the Subscription tab.'),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_subscription_display') ?: 'live',
      '#states' => $pages_enabled,
    ];
    $form['pages']['recurly_subscription_max'] = [
      '#title' => t('Multiple plans'),
      '#type' => 'radios',
      // Allows the number of plans to be some arbitrary amount in the future.
      '#options' => [
        '1' => t('Single-plan mode'),
        '0' => t('Multiple-plan mode'),
      ],
      '#description' => t('Single-plan mode allows users are only one subscription at a time, preventing them from having multiple plans active at the same time. If users are allowed to sign up for more than one subscription, use Multiple-plan mode.'),
      '#access' => count($plan_options),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_subscription_max') ?: 1,
      '#states' => $pages_enabled,
    ];
    $form['pages']['recurly_subscription_upgrade_timeframe'] = [
      '#title' => t('Upgrade plan behavior'),
      '#type' => 'radios',
      '#options' => [
        'now' => t('Upgrade immediately (pro-rating billing period usage)'),
        'renewal' => t('On next renewal'),
      ],
      '#access' => count($plan_options) > 1,
      '#description' => t('Affects users who are able to change their own plan (if more than one is enabled). Overriddable when changing plans as users with "Administer Recurly" permission.') . ' ' . t('An upgrade is considered moving to any plan that costs more than the current plan (regardless of billing cycle).'),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_subscription_upgrade_timeframe') ?: 'now',
      '#states' => $pages_enabled,
    ];
    $form['pages']['recurly_subscription_downgrade_timeframe'] = [
      '#title' => t('Downgrade plan behavior'),
      '#type' => 'radios',
      '#options' => [
        'now' => t('Downgrade immediately (pro-rating billing period usage)'),
        'renewal' => t('On next renewal'),
      ],
      '#access' => count($plan_options) > 1,
      '#description' => t('Affects users who are able to change their own plan (if more than one is enabled). Overriddable when changing plans as users with "Administer Recurly" permission.') . ' ' . t('An downgrade is considered moving to any plan that costs less than the current plan (regardless of billing cycle).'),
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_subscription_downgrade_timeframe') ?: 'renewal',
      '#states' => $pages_enabled,
    ];
    $form['pages']['recurly_subscription_cancel_behavior'] = [
      '#title' => t('Cancel plan behavior'),
      '#type' => 'radios',
      '#options' => [
        'cancel' => t('Cancel at renewal (leave active until end of period)'),
        'terminate_prorated' => t('Terminate immediately (prorated refund)'),
        'terminate_full' => t('Terminate immediately (full refund)'),
      ],
      '#description' => t('Affects users canceling their own subscription plans. Overriddable when canceling plans as users with "Administer Recurly" permission. Note that this behavior is also used when content associated with a Recurly account is deleted, or when users associated with an account are canceled.'),
      '#enabled' => count($plan_options) > 1,
      '#default_value' => \Drupal::config('recurly.settings')->get('recurly_subscription_cancel_behavior') ?: 'cancel',
      '#states' => $pages_enabled,
    ];

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $keys = [
      'recurly_private_api_key',
      'recurly_public_key',
      'recurly_subdomain',
      'recurly_listener_key',
    ];
    foreach ($keys as $key) {
      $form_state->setValue([$key], trim($form_state->getValue([$key])));
    }

    // Check that the API key is valid.
    if ($form_state->getValue(['recurly_private_api_key'])) {
      try {
        $settings = [
          'api_key' => $form_state->getValue([
            'recurly_private_api_key'
            ]),
          'public_key' => $form_state->getValue(['recurly_public_key']),
          'subdomain' => $form_state->getValue([
            'recurly_subdomain'
            ]),
        ];
        recurly_client_initialize($settings, TRUE);
        $plans = recurly_subscription_plans();
      }

        catch (Recurly_UnauthorizedError $e) {
        $form_state->setErrorByName('recurly_private_api_key', t('Your API Key is not authorized to connect to Recurly.'));
      }
    }
  }

  public function _submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Rebuild the menu system if any of the built-in page options change.
    foreach ($form_state->get(['pages_previous_values']) as $variable_name => $previous_value) {
      if (!$form_state->getValue([$variable_name]) && $form_state->getValue([$variable_name]) !== $previous_value) {
        menu_rebuild();
      }
    }
  }
}