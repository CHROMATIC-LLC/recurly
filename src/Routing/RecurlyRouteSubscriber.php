<?php

/**
 * @file
 * Contains \Drupal\recurly\Routing\RecurlyRouteSubscriber.
 */

namespace Drupal\recurly\Routing;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Recurly routes.
 */
class RecurlyRouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $entity_type_id = \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: 'user';
    $entity_manager_definitions = $this->entityManager->getDefinitions();
    $entity_type = $entity_manager_definitions[$entity_type_id];
    if ($entity_type->hasLinkTemplate('recurly-subscriptionlist') || $entity_type->hasLinkTemplate('recurly-signup') || $entity_type->hasLinkTemplate('recurly-change') || $entity_type->hasLinkTemplate('recurly-billing')) {

      $options = array(
        '_admin_route' => TRUE,
        '_recurly_entity_type_id' => $entity_type_id,
        'parameters' => array(
          $entity_type_id => array(
            'type' => 'entity:' . $entity_type_id,
          ),
        ),
      );

      if ($recurly_subscriptionlist = $entity_type->getLinkTemplate('recurly-subscriptionlist')) {
        $route = new Route(
          $recurly_subscriptionlist,
          array(
            '_controller' => '\Drupal\recurly\Controller\RecurlySubscriptionListController::subscriptionList',
            '_title' => \Drupal::config('recurly.settings')->get('recurly_subscription_max') == 1 ? 'Subscription Summary' : 'Subscription List',
          ),
          array(
            '_permission' => 'manage recurly subscription',
            // Is this line needed.
           // '_entity_access' => 'entity.update',.
            '_access_check_recurly_user' => 'TRUE',
            '_access_check_recurly_list' => 'TRUE',
          ),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_subscriptionlist", $route);
      }

      if ($recurly_change = $entity_type->getLinkTemplate('recurly-change')) {
        $route = new Route(
          $recurly_change,
          array(
            // @todo. Need a correct controller method.
            '_controller' => '\Drupal\recurly\Controller\RecurlyController::entityLoad',
            '_title' => 'Subscription plan',
          ),
          // @todo. What is the correct permission?
          array('_permission' => 'manage recurly subscription'),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_change", $route);
      }

      if ($recurly_signup = $entity_type->getLinkTemplate('recurly-signup')) {
        $route = new Route(
          $recurly_signup,
          array(
            // @todo. Need a correct controller method.
            '_controller' => '\Drupal\recurly\Controller\RecurlySubscriptionSelectPlanController::planSelect',
            '_title' => \Drupal::config('recurly.settings')->get('recurly_subscription_max') == 1 ? 'Signup' : 'Add plan',
            'operation' => 'select_plan',
          ),
          // @todo. What is the correct permission?
          array('_permission' => 'manage recurly subscription'),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_signup", $route);
      }

      if ($recurly_billing = $entity_type->getLinkTemplate('recurly-billing')) {
        $route = new Route(
          $recurly_billing,
          array(
            // @todo. Need a correct controller method.
            '_controller' => '\Drupal\recurly\Controller\RecurlyController::entityLoad',
            '_title' => 'Subscription Billing',
          ),
          // @todo. What is the correct permission?
          array('_permission' => 'manage recurly subscription'),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_billing", $route);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = array('onAlterRoutes', 100);
    return $events;
  }

}
