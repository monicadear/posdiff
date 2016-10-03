<?php
/**
 * @file
 * Invite tokens.
 */

use Drupal\Core\Render\BubbleableMetadata;
Use \Drupal\Core\Link;

/**
 * Implements hook_token_info().
 */
function invite_by_email_token_info() {
  $info = array();
  $info['types']['invite'] = array(
    'name' => t('Invite'),
    'description' => ('Invite by email tokens.'),
  );

  $info['tokens']['invite']['inviter:name'] = array(
    'name' => t('Inviter name'),
    'description' => t('Name of the invitation creator.'),
    'dynamic' => TRUE,
  );

  $info['tokens']['invite']['invite-accept-link'] = array(
    'name' => t('Accept link'),
    'description' => t('Link to accept the invitation.'),
    'dynamic' => TRUE,
  );

  return $info;
}

/**
 * Implements hook_tokens().
 */
function invite_by_email_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = array();
  if ($type == 'invite') {

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'inviter:name':
          $replacements[$original] = $data['invite']->getOwner()->getAccountName();
          break;
        case 'invite-accept-link':
          $replacements[$original] = Link::createFromRoute('accept invite', 'invite.invite_accept_accept', array('invite' => $data['invite']->getRegCode()), array('absolute' => TRUE))->getUrl()->toString();
          break;
        default:
      }
    }
  }

  return $replacements;
}