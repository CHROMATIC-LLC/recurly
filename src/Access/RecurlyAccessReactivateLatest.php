<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccessReactivateLatest.
 */

namespace Drupal\recurly\Access;

use Drupal\Core\Access\AccessResult;

/**
 * Checks if the reactivate latest operation should be accessible.
 */
class RecurlyAccessReactivateLatest extends RecurlyAccess {

  /**
   * {@inheritdoc}
   */
  public function access() {
    if ($this->recurlySubscriptionMax == 1) {
      $this->setLocalAccount();
      $active_subscriptions = $this->localAccount ? recurly_account_get_subscriptions($this->localAccount->account_code, 'active') : [];
      $active_subscription = reset($active_subscriptions);
      if (!empty($this->localAccount) && !empty($active_subscription) && $active_subscription->state == 'canceled') {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
