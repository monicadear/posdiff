<?php


namespace Drupal\invite_link\Plugin\Invite;


use Drupal\invite\InvitePluginInterface;

/**
 * Example plugin. Simplest use case.
 *
 * @Plugin(
 *   id="invite_link",
 *   label = @Translation("Invite Link")
 * )
 */
class InviteLink implements InvitePluginInterface {
  public function send($invite) {
    // Intentionally empty. This plugin only generates a link.
  }
}