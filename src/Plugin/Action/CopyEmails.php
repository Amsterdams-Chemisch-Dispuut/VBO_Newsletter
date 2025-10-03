<?php

namespace Drupal\vbo_newsletter\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Collects emails from selected users and outputs them as a comma-separated list.
 *
 * @Action(
 *   id = "vbo_newsletter_copy_emails",
 *   label = @Translation("Copy emails to clipboard"),
 *   type = "user"
 * )
 */
class CopyEmails extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // DO NOTHING HERE for batch safety. All processing happens in executeMultiple().
    return;
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $emails = [];

    // Collect emails.
    foreach ($entities as $entity) {
      if ($entity->hasField('mail') && !$entity->get('mail')->isEmpty()) {
        $emails[] = $entity->get('mail')->value;
      }
    }

    if (!empty($emails)) {
      $email_string = implode(',', $emails);

      // Output message. This is safe in a batch process.
      $this->messenger()->addMessage($this->t('Collected emails: @emails', [
        '@emails' => $email_string,
      ]));
    }

    // DO NOT return anything!
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // Only allow admins or users with view access.
    return $account->hasPermission('administer users')
      ? TRUE
      : $object->access('view', $account, $return_as_object);
  }

}
