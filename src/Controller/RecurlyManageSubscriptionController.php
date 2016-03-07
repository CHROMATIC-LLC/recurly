<?php
/**
 * @file
 * Contains \Drupal\recurly\Controller\RecurlyManageSubscriptionController.
 */

namespace Drupal\recurly\Controller;

use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Default controller for the recurly module.
 */
class RecurlyManageSubscriptionController extends RecurlyControllerBase {

  /**
   * Redirects a Recurly account code subscription management page.
   *
   * @param string $account_code
   *   The account code in the recurly_account table.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function subscriptionRedirect($account_code) {
    $entity_type_id = $this->recurlyConfig->entityType();
    $account = recurly_account_load(['account_code' => $account_code], TRUE);
    if ($account) {
      return $this->redirect("entity.$entity_type_id.recurly_signup", [$entity_type_id => $account->entity_id]);
    }
    else {
      throw new NotFoundHttpException();
    }
  }

}
