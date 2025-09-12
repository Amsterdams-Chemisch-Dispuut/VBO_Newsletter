<?php

namespace Drupal\vbo_newsletter\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Sends a custom email to selected users.
 *
 * @Action(
 *   id = "send_user_email",
 *   label = @Translation("Send email to selected users"),
 *   type = "user",
 *   confirm_form_route_name = "vbo_user_email.send_email_form"
 * )
 */
class SendUserEmail extends ActionBase implements ContainerFactoryPluginInterface {

  protected MailManagerInterface $mailManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailManagerInterface $mail_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailManager = $mail_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.mail')
    );
  }

  public function execute($entity = NULL) {
    // Nothing here; the form handles sending emails.
  }

  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object instanceof UserInterface ? TRUE : FALSE;
  }

}

?>