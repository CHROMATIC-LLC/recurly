<?php

/**
 * @file
 * Contains \Drupal\recurly\RecurlyConfigManager.
 */

namespace Drupal\recurly;

/**
 * Default Recurly entity type.
 */
const RECURLY_ENTITY_TYPE_DEFAULT = 'user';


class RecurlyConfigManager {

  /**
   * Retrieve the entity type configured to be used with Recurly.
   *
   * @return string
   *   An entity id string.
   */
  public function entityType() {
    return \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: self::RECURLY_ENTITY_TYPE_DEFAULT;
  }

}
