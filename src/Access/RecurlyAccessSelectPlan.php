<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccessSelectPlan.
 */

namespace Drupal\recurly\Access;

use Drupal\Core\Access\AccessResult;

/**
 * Checks if the select operation should be accessible.
 */
class RecurlyAccessSelectPlan extends RecurlyAccess {

  /**
   * {@inheritdoc}
   */
  public function access() {
    $this->setLocalAccount();
    if ($this->localAccount || $this->subscriptionPlans) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
