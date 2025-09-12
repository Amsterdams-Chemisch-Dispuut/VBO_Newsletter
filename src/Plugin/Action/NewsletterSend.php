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
/**
 * Provides a VBO action to send email to selected users.
 *
 * @Action(
 *   id = "send_user_email",
 *   label = @Translation("Send email to selected users"),
 *   type = "user",
 *   confirm_form_route_name = "vbo_newsletter.send_email_form"
 * )
 */
 */
class NewsletterSend extends ActionBase implements ContainerFactoryPluginInterface {

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

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // This method is required for VBO compatibility.
    // The actual sending is handled by the confirmation form.
    // Optionally, you could add logging or other logic here.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // Only allow action for user entities.
    $result = $object instanceof UserInterface;
    return $return_as_object ? $this->wrapAccessResult($result) : $result;
  }

  /**
   * Helper for Drupal 11 access result wrapping.
   */
  protected function wrapAccessResult($result) {
    if (class_exists('Drupal\\Core\\Access\\AccessResult')) {
      return \Drupal\Core\Access\AccessResult::allowedIf($result);
    }
    return $result;
  }

}

?>
