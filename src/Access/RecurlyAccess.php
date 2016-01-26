<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccess.
 */


namespace Drupal\recurly\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;

/**
 * Recurly access check abstract class for shared functionality.
 */
abstract class RecurlyAccess implements AccessInterface {
  protected $subscriptionPlans;
  protected $recurlySubscriptionMax;
  protected $localAccount;
  protected $entityType;
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
    $this->entityType = \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: 'user';
    $this->subscriptionPlans = \Drupal::config('recurly.settings')->get('recurly_subscription_plans') ?: [];
    $this->recurlySubscriptionMax = \Drupal::config('recurly.settings')->get('recurly_subscription_max');
  }
  
  /**
   * {@inheritdoc}
   */
  public function access() {
  }

  /**
   * Determine if this is a signup path.
   *
   * @param Symfony\Component\Routing\Route $route
   *   A Route object.
   *
   * @return bool
   *   TRUE if the path contains 'signup', else FALSE.
   */
  protected function pathIsSignup(Route $route) {
    if (strpos($route->getPath(), 'signup') !== FALSE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Loads the Recurly account.
   */
  protected function setLocalAccount() {
    $entity = $this->routeMatch->getCurrentRouteMatch()->getParameter($this->entityType);
    $entity_id = method_exists($entity, 'id') ? $entity->id() : NULL;
    $this->localAccount = recurly_account_load(['entity_type' => $this->entityType, 'entity_id' => $entity_id], TRUE);
  }

}
