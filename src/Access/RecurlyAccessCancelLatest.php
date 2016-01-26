<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccessCancelLatest.
 */

namespace Drupal\recurly\Access;

use Drupal\Core\Access\AccessResult;

/**
 * Checks if the cancel operation should be accessible.
 */
class RecurlyAccessCancelLatest extends RecurlyAccess {

  /**
   * {@inheritdoc}
   */
  public function access() {
    if ($this->recurlySubscriptionMax == 1) {
      $this->setLocalAccount();
      $active_subscriptions = $this->localAccount ? recurly_account_get_subscriptions($this->localAccount->account_code, 'active') : [];
      $active_subscription = reset($active_subscriptions);
      if (!empty($this->localAccount) && !empty($active_subscription) && $active_subscription->state == 'active') {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
