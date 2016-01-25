<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccessSelectPlan.
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
class RecurlyAccessSelectPlan extends RecurlyAccess {

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, RouteMatchInterface $route_match, EntityInterface $entity) {
    if ($this->localAccount || $this->subscriptionPlans) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

}
