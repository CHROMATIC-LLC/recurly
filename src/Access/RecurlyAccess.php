<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccess.
 */


namespace Drupal\recurly\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\EventSubscriber\EntityRouteAlterSubscriber;
use Symfony\Component\Routing\Route;

/**
 * Recurly access check abstract class for shared functionality.
 */
abstract class RecurlyAccess implements AccessInterface {
  protected $subscriptionPlans;
  protected $recurlySubscriptionMax;
  protected $localAccount;
  protected $entityType;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityType = \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: 'user';
    $this->subscriptionPlans = \Drupal::config('recurly.settings')->get('recurly_subscription_plans') ?: [];
    $this->recurlySubscriptionMax = \Drupal::config('recurly.settings')->get('recurly_subscription_max');
  }
  
  /**
   * {@inheritdoc}
   */
  public function access(Route $route, RouteMatchInterface $route_match) {
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

  protected function getLocalAccount(EntityInterface $entity, $entity_type) {
    $this->localAccount = recurly_account_load(['entity_type' => $entity_type, 'entity_id' => $entity->id()], TRUE);
  }

}
