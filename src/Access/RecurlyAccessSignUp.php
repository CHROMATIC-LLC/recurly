<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccessSignUp.
 */

namespace Drupal\recurly\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks if the sign up operation should be accessible.
 */
class RecurlyAccessSignUp extends RecurlyAccess {

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, RouteMatchInterface $route_match) {

    if ($this->recurlySubscriptionMax == 1) {
      $entity = $route_match->getParameter($this->entityType);
      $this->getLocalAccount($entity, $this->entityType);
      $active_subscriptions = $this->localAccount ? recurly_account_get_subscriptions($this->localAccount->account_code, 'active') : [];
      if (isset($this->localAccount) || isset($active_subscriptions)) {
        return AccessResult::allowed();
      }
    }
    elseif ($this->recurlySubscriptionMax != 1) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
