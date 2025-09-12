<?php

namespace Drupal\vbo_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NewsletterForm extends FormBase {

  protected MailManagerInterface $mailManager;

  public function __construct(MailManagerInterface $mail_manager) {
    $this->mailManager = $mail_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.mail'));
  }

  public function getFormId() {
    return 'send_user_email_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $users = NULL) {
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
    ];
    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#required' => TRUE,
    ];
    $form['users'] = [
      '#type' => 'hidden',
      '#value' => implode(',', $users ?? []),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send email'),
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $users = explode(',', $form_state->getValue('users'));
    $subject = $form_state->getValue('subject');
    $body = $form_state->getValue('body');

    foreach ($users as $uid) {
      $user = \Drupal\user\Entity\User::load($uid);
      if ($user instanceof UserInterface) {
        $this->mailManager->mail(
          'vbo_newsletter',
          'send_email',
          $user->getEmail(),
          $user->getPreferredLangcode(),
          ['subject' => $subject, 'message' => $body],
          NULL,
          TRUE
        );
      }
    }
    $this->messenger()->addMessage($this->t('Emails sent to selected users.'));
  }
}
?>