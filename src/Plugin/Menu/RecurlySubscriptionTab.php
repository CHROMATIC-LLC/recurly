<?php

/**
 * @file
 * Contains \Drupal\recurly\Plugin\Menu\RecurlySubscriptionTab.
 */

namespace Drupal\recurly\Plugin\Menu;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Defines dynamic local tasks.
 */
class RecurlySubscriptionTab extends LocalTaskDefault {

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    $parameters = parent::getRouteParameters($route_match);

    $entity_type = \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: 'user';
    if ($entity = $route_match->getParameter($entity_type)) {
      return ['entity' => $entity->id()];
    }
    return [];
  }

}
