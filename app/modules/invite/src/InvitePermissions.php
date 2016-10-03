<?php

namespace Drupal\invite;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the invite module.
 */
class InvitePermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new InvitePermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'));
  }

  /**
   * Returns an array of invite permissions.
   *
   * @return array
   */
  public function permissions() {
    $permissions = [];
    // Generate permissions for each invite type.
    $invite_types = $this->entityManager->getStorage('invite_type')
      ->loadMultiple();
    foreach ($invite_types as $invite_type) {
      $permissions['invite_type_' . $invite_type->getType()] = array(
        'title' => $this->t('Create @label invites', array('@label' => $invite_type->label())),
        'description' => array(
          '#prefix' => '<em>',
          '#markup' => $this->t('Warning: This permission could have security implications.'),
          '#suffix' => '</em>',
        ),
      );
    }
    return $permissions;
  }

}
