<?php
/**
 * Contains \Drupal\invite\InvitePluginInterface.
 */

namespace Drupal\invite;

/**
 * For creating invite sending method plugins.
 *
 * Plugin should set the plugin machine name on the invite it creates in order for
 * the send method to be called. e.g.
 *
 * $invite->setPlugin('invite_link');
 * @see \Drupal\invite_link\Form\InviteLinkBlockForm->submitForm for an example.
 *
 * @Plugin(
 *   id="Name of Invite plugin"
 * )
 *
 */
interface InvitePluginInterface {

  /**
   * Plugin send method.
   *
   * @param $invite
   *  The invite entity.
   */
  public function send($invite);
}