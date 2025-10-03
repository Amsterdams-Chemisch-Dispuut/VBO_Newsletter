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
    if (!$entity) {
      return;
    }

    // Only collect email if available.
    if ($entity->hasField('mail') && !$entity->get('mail')->isEmpty()) {
      $emails[] = $entity->get('mail')->value;
      // Immediately output via messenger.
      $this->messenger()->addMessage($this->t('Collected email: @email', [
        '@email' => $entity->get('mail')->value,
      ]));
    }

    // DO NOT return anything.
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    // Collect emails and output immediately.
    $emails = [];
    foreach ($entities as $entity) {
      if ($entity->hasField('mail') && !$entity->get('mail')->isEmpty()) {
        $emails[] = $entity->get('mail')->value;
      }
    }

    if (!empty($emails)) {
      $email_string = implode(',', $emails);
      $this->messenger()->addMessage($this->t('Collected emails: @emails', [
        '@emails' => $email_string,
      ]));
    }

    // DO NOT return anything.
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $account->hasPermission('administer users')
      ? TRUE
      : $object->access('view', $account, $return_as_object);
  }

}
