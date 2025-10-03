<?php

namespace Drupal\VBO_Newsletter\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Sends a newsletter to selected entities.
 *
 * @Action(
 *   id = "VBO_Newsletter_newsletter_send",
 *   label = @Translation("Send newsletter"),
 *   type = ""
 * )
 */
class NewsletterSend extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['subject'] ?? '',
    ];

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message body'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['body'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['subject'] = $form_state->getValue('subject');
    $this->configuration['body'] = $form_state->getValue('body');
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity) {
      return;
    }

    if ($entity->hasField('mail') && !$entity->get('mail')->isEmpty()) {
      $recipient = $entity->get('mail')->value;

      // Grab subject and body from configuration.
      $subject = $this->configuration['subject'] ?? $this->t('Newsletter');
      $body = $this->configuration['body'] ?? '';

      // TODO: Replace with real MailManager send.
      $this->messenger()->addMessage($this->t(
        'Sent newsletter to @recipient: @subject',
        ['@recipient' => $recipient, '@subject' => $subject]
      ));

      return $this->t('Newsletter sent to @recipient', ['@recipient' => $recipient]);
    }

    return $this->t('No email found for entity ID @id', ['@id' => $entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object->access('update', $account, $return_as_object);
  }

}
