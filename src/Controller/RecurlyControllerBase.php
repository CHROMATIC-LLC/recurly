<?php

/**
 * @file
 * Contains \Drupal\recurly\Controller\RecurlyControllerBase.
 */

namespace Drupal\recurly\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\recurly\RecurlyConfigManager;
use Drupal\recurly\RecurlyFormatManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Recurly base controller
 */
abstract class RecurlyControllerBase extends ControllerBase {

  /**
   * The formatting service.
   *
   * @var \Drupal\recurly\RecurlyFormatManager
   */
  protected $recurlyFormatter;

  /**
   * The config service.
   *
   * @var \Drupal\recurly\RecurlyConfigManager
   */
  protected $recurlyConfig;

  /**
   * Constructs a \Drupal\recurly\Controller\RecurlyControllerBase object.
   *
   * @param \Drupal\recurly\RecurlyFormatManager $recurly_formatter
   *   The Recurly formatter to be used for formatting.
   * @param \Drupal\recurly\RecurlyConfigManager $recurly_config
   *   Recurly configuration manager.
   */
  public function __construct(RecurlyFormatManager $recurly_formatter, RecurlyConfigManager $recurly_config) {
    $this->recurlyFormatter = $recurly_formatter;
    $this->recurlyConfig = $recurly_config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('recurly.format_manager'),
      $container->get('recurly.config_manager')
    );
  }

}
