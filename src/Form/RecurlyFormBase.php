<?php

/**
 * @file
 * Contains \Drupal\recurly\Form\RecurlyFormBase.
 */

namespace Drupal\recurly\Form;

use Drupal\Core\Form\FormBase;
use Drupal\recurly\RecurlyConfigManager;
use Drupal\recurly\RecurlyFormatManager;
use Drupal\recurly\RecurlyUrlManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Recurly configuration settings form.
 */
abstract class RecurlyFormBase extends FormBase {

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
   * The Recurly Url service.
   *
   * @var \Drupal\recurly\RecurlyUrlManager
   */
  protected $recurlyUrlManager;

  /**
   * Constructs a \Drupal\recurly\Form\RecurlyFormBase object.
   *
   * @param \Drupal\recurly\RecurlyFormatManager $recurly_formatter
   *   The Recurly formatter to be used for formatting.
   * @param \Drupal\recurly\RecurlyConfigManager $recurly_config
   *   Recurly configuration manager.
   * @param \Drupal\recurly\RecurlyUrlManager $recurly_url_manager
   *   Recurly Url manager.
   */
  public function __construct(RecurlyFormatManager $recurly_formatter, RecurlyConfigManager $recurly_config, RecurlyUrlManager $recurly_url_manager) {
    $this->recurlyFormatter = $recurly_formatter;
    $this->recurlyConfig = $recurly_config;
    $this->recurlyUrlManager = $recurly_url_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('recurly.format_manager'),
      $container->get('recurly.config_manager'),
      $container->get('recurly.url_manager')
    );
  }

}
