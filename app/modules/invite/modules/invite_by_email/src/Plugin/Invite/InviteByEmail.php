<?php


namespace Drupal\invite_by_email\Plugin\Invite;


use Drupal\Core\Render\BubbleableMetadata;
use Drupal\invite\InvitePluginInterface;

/**
 * @Plugin(
 *   id="invite_by_email",
 *   label = @Translation("Invite By Email")
 * )
 */
class InviteByEmail implements InvitePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function send($invite) {
    /*
     * @var $token \Drupal\token\Token
     * @var $mail \Drupal\Core\Mail\MailManager
     */
    $bubbleable_metadata = new BubbleableMetadata();
    $token = \Drupal::service('token');
    $mail = \Drupal::service('plugin.manager.mail');
    $mail_key = $invite->get('type')->value;
    // Prepare message.
    $message = $mail->mail('invite_by_email', $mail_key, $invite->get('field_invite_email_address')->value, $invite->activeLangcode, array(), $invite->getOwner()
      ->getEmail());
    // If HTML email.
    if (unserialize(\Drupal::config('invite.invite_type.' . $invite->get('type')->value)
      ->get('data'))['html_email']
    ) {
      $message['headers']['Content-Type'] = 'text/html; charset=UTF-8;';
    }
    $message['subject'] = $token->replace($invite->get('field_invite_email_subject')->value, array('invite' => $invite), array(), $bubbleable_metadata);
    $body = array(
      '#theme' => 'invite_by_email',
      '#body' => $token->replace($invite->get('field_invite_email_body')->value, array('invite' => $invite), array(), $bubbleable_metadata),
    );
    $message['body'] = \Drupal::service('renderer')
      ->render($body)
      ->__toString();
    // Send.
    $system = $mail->getInstance(array(
      'module' => 'invite_by_email',
      'key' => $mail_key
    ));
    $system->mail($message);
  }

}