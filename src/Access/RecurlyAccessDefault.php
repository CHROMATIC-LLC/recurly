<?php
/**
 * @file
 * Contains \Drupal\recurly\Access\RecurlyAccessDefault.
 */

/**
 * Eventually each operation in this class will be put into its own class and
 * the routes will be updated to check services that interface with each of
 * these classes.
 */

namespace Drupal\recurly\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;

/**
 * Checks access for displaying a given operation.
 */
class RecurlyAccessDefault extends RecurlyAccess {

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, RouteMatchInterface $route_match, $operation = NULL) {
    $entity_type_id = \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: 'user';
    $entity = $route_match->getParameter($entity_type_id);
    $local_account = recurly_account_load(['entity_type' => $entity->getEntityType()->getLowercaseLabel(), 'entity_id' => $entity->id()], TRUE);
    if (!empty($this->localAccount)) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }

}
