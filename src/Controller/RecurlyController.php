<?php

/**
 * @file
 * Contains \Drupal\recurly\Controller\RecurlyController.
 */

namespace Drupal\recurly\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Returns responses for recurly module routes.
 */
class RecurlyController extends ControllerBase {

  /**
   * Builds the entity types overview page.
   *
   * @return array
   *   Array of page elements to render.
   */
  public function entityInfoPage() {
    return array('#markup' => '<h1>We need the right markup here</h1>');
  }

  /**
   * Prints the loaded structure of the current entity.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   A RouteMatch object.
   *
   * @return array
   *   Array of page elements to render.
   */
  public function entityLoad(RouteMatchInterface $route_match) {
    $output = array();

    $parameter_name = $route_match->getRouteObject()->getOption('_recurly_entity_type_id');
    $entity = $route_match->getParameter($parameter_name);

    if ($entity && $entity instanceof EntityInterface) {
      $output = array('#markup' => '<h1>We need the right markup Load</h1>');
    }

    return $output;
  }

  /**
   * Prints the render structure of the current entity.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   A RouteMatch object.
   *
   * @return array
   *   Array of page elements to render.
   */
  public function entityRender(RouteMatchInterface $route_match) {
    $output = array();

    $parameter_name = $route_match->getRouteObject()->getOption('_recurly_entity_type_id');
    $entity = $route_match->getParameter($parameter_name);

    if ($entity && $entity instanceof EntityInterface) {
      $output = array('#markup' => '<h1>We need the right markup render</h1>');
    }

    return $output;
  }
}
