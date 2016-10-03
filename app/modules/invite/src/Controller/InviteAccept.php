<?php

namespace Drupal\invite\Controller;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Entity;
use Drupal\invite\InviteAcceptEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InviteAccept.
 *
 * @package Drupal\invite\Controller
 */
class InviteAccept extends ControllerBase {

  public $dispatcher;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_dispatcher')
    );
  }

  public function __construct(ContainerAwareEventDispatcher $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * Accepts an invitation.
   */
  public function accept($invite) {
    $account = $this->currentUser();
    $redirect = '<front>';
    $message = 'Hmm.';
    $type = 'status';

    // Current user is the inviter.
    if ($account->id() == $invite->getOwnerId()) {
      $message = $this->t('You can\'t use your own invite...');
      $type = 'error';
    }
    // Invite has already been used.
    else if($invite->getStatus() == INVITE_USED) {
      $message = $this->t('Sorry this invitation has already been used.');
      $type = 'error';
    }
    // Good to go!
    else {
      $_SESSION['invite_code'] = $invite->getRegCode();
      $redirect = 'user.register';
      $message = $this->t('Please create an account to accept the invitation.');
    }

    // Let other modules act on the invite accepting before the user is created.
    $invite_accept = new InviteAcceptEvent(array(
      'redirect' => &$redirect,
      'message' => &$message,
      'type' => &$type,
      'invite' => &$invite,
    ));

    $this->dispatcher->dispatch('invite_accept', $invite_accept);
    drupal_set_message($message, $type);

    return $this->redirect($redirect);
  }

}
