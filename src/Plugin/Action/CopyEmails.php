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
   * Temporary storage for collected emails.
   *
   * @var array
   */
  protected $emails = [];

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity) {
      return;
    }

    // Make sure the entity has an email field.
    if ($entity->hasField('mail') && !$entity->get('mail')->isEmpty()) {
      $this->emails[] = $entity->get('mail')->value;
    }

    // DO NOT return anything.
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $this->emails = [];

    foreach ($entities as $entity) {
      $this->execute($entity);
    }

    // Join emails with commas.
    $email_string = implode(',', $this->emails);

    // Display in a Drupal message.
    $this->messenger()->addMessage($this->t('Collected emails: @emails', [
      '@emails' => $email_string,
    ]));

    // DO NOT return anything.
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // Only allow access to admins or users who can view email.
    return $account->hasPermission('administer users')
      ? TRUE
      : $object->access('view', $account, $return_as_object);
  }

}
