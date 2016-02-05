<?php

/**
 * @file
 * Contains \Drupal\recurly\Form\RecurlyFormBase.
 */

namespace Drupal\recurly\Form;

use Drupal\Core\Form\FormBase;
use Drupal\recurly\RecurlyConfigManager;
use Drupal\recurly\RecurlyFormatManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Recurly configuration settings form.
 */
abstract class RecurlyFormBase extends FormBase {

  /**
   * The config service.
   *
   * @var \Drupal\recurly\RecurlyConfigManager
   */
  protected $recurlyConfig;

  /**
   * The formatting service.
   *
   * @var \Drupal\recurly\RecurlyFormatManager
   */
  protected $recurlyFormatter;

  /**
   * Constructs a \Drupal\recurly\Form\RecurlyFormBase object.
   *
   * @param RecurlyConfigManager $recurly_config
   *   Recurly configuration manager.
   * @param \Drupal\recurly\RecurlyFormatManager $recurly_formatter
   *   The Recurly formatter to be used for formatting.
   */
  public function __construct(RecurlyConfigManager $recurly_config, RecurlyFormatManager $recurly_formatter) {
    $this->recurlyConfig = $recurly_config;
    $this->recurlyFormatter = $recurly_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('recurly.config_manager'),
      $container->get('recurly.format_manager')
    );
  }

}
