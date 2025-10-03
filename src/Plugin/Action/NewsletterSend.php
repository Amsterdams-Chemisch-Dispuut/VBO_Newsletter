<?php

namespace Drupal\vbo_newsletter\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\Markup;

/**
 * Sends a newsletter to selected entities.
 *
 * @Action(
 *   id = "vbo_newsletter_send",
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
  public function needsConfiguration() {
    return TRUE;
  }

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

      $subject = $this->configuration['subject'] ?? $this->t('Newsletter');
      $body = $this->configuration['body'] ?? '';

      /** @var \Drupal\Core\Mail\MailManagerInterface $mailManager */
      $mailManager = \Drupal::service('plugin.manager.mail');
      $langcode = \Drupal::currentUser()->getPreferredLangcode();

      $params = [
        'subject' => $subject,
        'body' => $body,
      ];

      $result = $mailManager->mail(
        'vbo_newsletter',       // module key
        'newsletter_send',      // message key
        $recipient,             // to
        $langcode,
        $params,
        NULL,
        TRUE
      );

      if ($result['result'] !== TRUE) {
        $this->messenger()->addError($this->t('There was a problem sending the newsletter to @recipient.', [
          '@recipient' => $recipient,
        ]));
      }
      else {
        $this->messenger()->addMessage($this->t('Newsletter sent to @recipient.', [
          '@recipient' => $recipient,
        ]));
      }

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
