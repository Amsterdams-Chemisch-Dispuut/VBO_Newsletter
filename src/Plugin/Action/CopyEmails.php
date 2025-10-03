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

    // We don’t return anything yet; we’ll handle it in executeMultiple().
    return;
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $this->emails = [];

    // Call the default execute() for each entity.
    foreach ($entities as $entity) {
      $this->execute($entity);
    }

    // Join emails with commas.
    $email_string = implode(',', $this->emails);

    // Output to a "clipboard-friendly" modal / message.
    // Drupal messenger will show it; users can copy manually.
    $this->messenger()->addMessage($this->t('Collected emails: @emails', [
      '@emails' => $email_string,
    ]));
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
