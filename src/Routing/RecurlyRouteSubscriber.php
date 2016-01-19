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
            '_entity_access' => "$entity_type_id.update",
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
            '_controller' => '\Drupal\recurly\Controller\RecurlySubscriptionSelectPlanController::planSelect',
            '_title' => 'Change plan',
            'subscription_id' => 'latest',
            'operation' => 'change_plan_latest',
          ),
          // @todo. What is the correct permission?
          array(
            '_entity_access' => "$entity_type_id.update",
            '_access_check_recurly_user' => 'TRUE',
            '_access_check_recurly_default' => 'TRUE',
          ),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_change", $route);
      }

      if ($recurly_planchange = $entity_type->getLinkTemplate('recurly-planchange')) {
        $route = new Route(
          $recurly_planchange,
          array(
            '_controller' => '\Drupal\recurly\Controller\RecurlySubscriptionChangeController::changePlan',
            '_title' => 'Change subscription',
            'operation' => 'change_plan',
          ),
          // @todo. What is the correct permission?
          array(
            '_entity_access' => "$entity_type_id.update",
            '_access_check_recurly_user' => 'TRUE',
            '_access_check_recurly_default' => 'TRUE',
          ),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_planchange", $route);
      }

      if ($recurly_signup = $entity_type->getLinkTemplate('recurly-signup')) {
        $route = new Route(
          $recurly_signup,
          array(
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
      if ($recurly_cancel_latest = $entity_type->getLinkTemplate('recurly-cancellatest')) {
        $route = new Route(
          $recurly_cancel_latest,
          array(
            '_controller' => '\Drupal\recurly\Controller\RecurlySubscriptionCancelController::subscriptionCancel',
            '_title' => 'Cancel subscription',
            'subscription_id' => 'latest',
            'operation' => 'cancel_latest',
          ),
          array(
            '_entity_access' => "$entity_type_id.update",
            '_access_check_recurly_user' => 'TRUE',
            '_access_check_recurly_default' => 'TRUE',
          ),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_cancellatest", $route);
      }
      if ($recurly_cancel = $entity_type->getLinkTemplate('recurly-cancel')) {
        $route = new Route(
          $recurly_cancel,
          array(
            '_controller' => '\Drupal\recurly\Controller\RecurlySubscriptionCancelController::subscriptionCancel',
            '_title' => 'Cancel subscription',
            'subscription_id' => 'latest',
            'operation' => 'cancel',
          ),
          array(
            '_entity_access' => "$entity_type_id.update",
            '_access_check_recurly_user' => 'TRUE',
            '_access_check_recurly_default' => 'TRUE',
          ),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_cancel", $route);
      }

      if ($recurly_reactivate_latest = $entity_type->getLinkTemplate('recurly-reactivatelatest')) {
        $route = new Route(
          $recurly_reactivate_latest,
          array(
            '_controller' => '\Drupal\recurly\Controller\RecurlySubscriptionReactivateController::reactivateSubscription',
            '_title' => 'Reactivate',
            'operation' => 'reactivate_latest',
          ),
          array(
            '_entity_access' => "$entity_type_id.update",
            '_access_check_recurly_user' => 'TRUE',
            '_access_check_recurly_default' => 'TRUE',
          ),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_reactivatelatest", $route);
      }
      if ($recurly_reactivate = $entity_type->getLinkTemplate('recurly-reactivate')) {
        $route = new Route(
          $recurly_reactivate,
          array(
            '_controller' => '\Drupal\recurly\Controller\RecurlySubscriptionReactivateController::reactivateSubscription',
            '_title' => 'Reactivate',
            'operation' => 'reactivate',
          ),
          array(
            '_entity_access' => "$entity_type_id.update",
            '_access_check_recurly_user' => 'TRUE',
            '_access_check_recurly_default' => 'TRUE',
          ),
          $options
        );

        $collection->add("entity.$entity_type_id.recurly_cancel", $route);
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
