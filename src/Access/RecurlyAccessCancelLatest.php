<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccessCancelLatest.
 */

namespace Drupal\recurly\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks if the select operation should be accessible.
 */
class RecurlyAccessCancelLatest extends RecurlyAccess {

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, RouteMatchInterface $route_match) {
    $entity = $route_match->getParameter($this->entityType);
    $this->getLocalAccount($entity, $this->entityType);
    if ($this->recurlySubscriptionMax == 1) {
      $active_subscriptions = $this->localAccount ? recurly_account_get_subscriptions($this->localAccount->account_code, 'active') : [];
      $active_subscription = reset($active_subscriptions);
      if (!empty($this->localAccount) && !empty($active_subscription) && $active_subscription->state == 'active') {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
