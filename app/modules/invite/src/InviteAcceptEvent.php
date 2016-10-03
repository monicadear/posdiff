<?php
/**
 * @file
 * Contains Drupal\invite\InviteAcceptEvent.
 */

namespace Drupal\invite;


use Symfony\Component\EventDispatcher\Event;

class InviteAcceptEvent extends Event {
  protected $invite_accept;

  public function __construct($invite_accept) {
    $this->invite_accept = $invite_accept;
  }

  public function getInviteAcceptEvent() {
    return $this->invite_accept;
  }

  public function setInviteAcceptEvent($invite_accept) {
    $this->invite_accept = $invite_accept;
  }

}