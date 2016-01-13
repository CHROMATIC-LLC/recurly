<?php

/**
 * @file
 * Contains \Drupal\recurly\Plugin\Derivative\RecurlyLocalTask.
 */

namespace Drupal\recurly\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all entity bundles.
 */
class RecurlyLocalTask extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity manager
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Creates an DevelLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, TranslationInterface $string_translation) {
    $this->entityManager = $entity_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = array();
    $entity_type_id = \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: 'user';
    $entity_manager_definitions = $this->entityManager->getDefinitions();
    $entity_type = $entity_manager_definitions[$entity_type_id];
    $has_edit_path = $entity_type->hasLinkTemplate('recurly-change');
    $has_canonical_path = $entity_type->hasLinkTemplate('recurly-subscriptionlist');

    $this->derivatives["$entity_type_id.recurly_tab"] = array(
      'route_name' => "entity.$entity_type_id." . ($has_edit_path ? 'recurly_subscriptionlist' : 'recurly_subscriptionlist'),
      'title' => $this->t('Subscription'),
      'base_route' => "entity.$entity_type_id." . ($has_canonical_path ? "canonical" : "edit_form"),
      'weight' => 100,
    );

    if ($has_canonical_path) {
      $this->derivatives["$entity_type_id.recurly_signup_tab"] = array(
        'route_name' => "entity.$entity_type_id.recurly_subscriptionlist",
        'title' => $this->t('Signup'),
        'parent_id' => "recurly.entities:$entity_type_id.recurly_tab",
        'weight' => 50,
      );

      $this->derivatives["$entity_type_id.recurly_billing_tab"] = array(
        'route_name' => "entity.$entity_type_id.recurly_billing",
        'title' => $this->t('Update billing information'),
        'parent_id' => "recurly.entities:$entity_type_id.recurly_tab",
        'weight' => 100,
      );
    }

    if ($has_edit_path) {
      $this->derivatives["$entity_type_id.recurly_change_tab"] = array(
        'route_name' => "entity.$entity_type_id.recurly_change",
        'weight' => 200,
        'title' => $this->t('Change plan'),
        'parent_id' => "recurly.entities:$entity_type_id.recurly_tab",
      );
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }
    return $this->derivatives;
  }
}
