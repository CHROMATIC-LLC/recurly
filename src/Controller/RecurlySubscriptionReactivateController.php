<?php

/**
 * @file
 * Contains \Drupal\recurly\Controller\RecurlySubscriptionReactivateController.
 */

namespace Drupal\recurly\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Recurly reactivate subscription controller.
 */
class RecurlySubscriptionReactivateController extends ControllerBase {

  /**
   * Reactivate the specified subscription.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity whose subscription is being reactivated.
   * @param string $subscription_id
   *   The UUID of the subscription to reactivate.
   */
  public function reactivateSubscription(RouteMatchInterface $route_match, $subscription_id = 'latest') {
    $entity_type_id = \Drupal::config('recurly.settings')->get('recurly_entity_type') ?: 'user';
    $entity = $route_match->getParameter($entity_type_id);
    // Initialize the Recurly client with the site-wide settings.
    if (!recurly_client_initialize()) {
      return ['#markup' => $this->t('Could not initialize the Recurly client.')];
    }
    $entity_type = $entity->getEntityType()->getLowercaseLabel();

    // Load the subscription.
    if ($subscription_id === 'latest') {
      $local_account = recurly_account_load(['entity_type' => $entity_type, 'entity_id' => $entity->id()], TRUE);
      $subscriptions = recurly_account_get_subscriptions($local_account->account_code, 'active');
      $subscription = reset($subscriptions);
    }
    else {
      try {
        $subscription = \Recurly_Subscription::get($subscription_id);
      }
      catch (\Recurly_NotFoundError $e) {
        drupal_set_message($this->t('Subscription not found'));
        throw new NotFoundHttpException();
      }
    }

    try {
      $subscription->reactivate();
      drupal_set_message($this->t('Plan @plan reactivated! Normal billing will resume on @date.', [
        '@plan' => $subscription->plan->name,
        '@date' => recurly_format_date($subscription->current_period_ends_at),
      ]));
    }
    catch (Recurly_Error $e) {
      drupal_set_message($this->t('The plan could not be reactivated because the billing service encountered an error.'));
      return;
    }

    return $this->redirect("entity.$entity_type.recurly_subscriptionlist", [$entity_type_id => $entity->id()]);
  }

}
