<?php

namespace Drupal\invite\Form;

use Drupal\Core\Block\BlockManager;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\invite\Entity\InviteSender;
use Drupal\invite\InvitePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Invite type edit forms.
 *
 * @package Drupal\invite\Form
 */
class InviteTypeForm extends EntityForm {

  /**
   * @var \Drupal\invite\InvitePluginManager
   */
  public $pluginManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.invite'),
      $container->get('plugin.manager.block'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityManagerInterface $entity_manager, InvitePluginManager $plugin_manager, BlockManager $block_plugin_manager, Connection $database) {
    $this->pluginManager = $plugin_manager;
    $this->database = $database;
    $this->block_manager = $block_plugin_manager;
  }

  /**
   * Helper function to load the default send method for the invite type.
   */
  public function getDefaultSendMethods($invite_type) {
    $defaults = array();
    foreach (explode('||', \Drupal::config('invite.invite_sender.' . $invite_type->getType())->get('sending_methods')) as $sending_method) {
      if ($sending_method != '0') {
        $defaults[$sending_method] = $sending_method;
      }
    }

    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\invite\Entity\InviteType */
    $form = parent::form($form, $form_state);
    $entity = $this->entity;
    $is_new = $entity->isNew();
    if ($is_new) {
      $entity
        ->set('label', '')
        ->set('type', '')
        ->set('description', '')
        ->set('data', '');

    }
    $data = unserialize($entity->getData());

    $form['label'] = array(
      '#title' => t('Invite Type Label'),
      '#type' => 'textfield',
      '#default_value' => $entity->label(),
      '#description' => t('The human-readable name of this invite type. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $entity->getType(),
      '#maxlength' => 255,
      '#disabled' => !$is_new,
      '#machine_name' => array(
        'exists' => ['Drupal\invite\Entity\InviteType', 'load'],
        'source' => array('label'),
      ),
      '#description' => t('A unique machine-readable name for this invite type. It must only contain lowercase letters, numbers, and underscores.'),
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#description' => t('Description about the invite type.'),
      '#rows' => 5,
      '#default_value' => $entity->getDescription(),
    );

    $options[] = '- ' . $this->t('None') . ' -';
    foreach (user_roles() as $user_role) {
      if (empty($user_role->get('_core'))) {
        $options[$user_role->id()] = $user_role->label();
      }
    }
    $form['target_role'] = array(
      '#type' => 'select',
      '#required' => FALSE,
      '#title' => t('Role'),
      '#description' => t('Please select a role to apply to the invitee (Optional).'),
      '#options' => $options,
      '#default_value' => $data['target_role'],
    );

    // List the available sending methods.
    $plugin_definitions = $this->pluginManager->getDefinitions();
    if (!empty($plugin_definitions)) {
      $options = array();
      foreach ($plugin_definitions as $plugin_definition) {
        $options[$plugin_definition['provider']] = $plugin_definition['id'];
      }
      $default_send_method = array();
      if (!$is_new) {
        $default_send_method = $this->getDefaultSendMethods($entity);
      }
      $form['send_method'] = array(
        '#type' => 'checkboxes',
        '#required' => TRUE,
        '#title' => t('Sending Method'),
        '#default_value' => $default_send_method,
        '#options' => $options,
      );
    }
    else {
      $form['send_method'] = array(
        '#type' => 'item',
        '#markup' => $this->t('Please enable a sending method module such as Invite by email.'),
      );
      $form['actions']['submit']['#disabled'] = TRUE;

    }

    return $form;
  }

  /**
   * Helper method to add an invite_sender record.
   */
  public function updateInviteSender($send_methods, $invite_type) {
    $type = $invite_type->getType();
    $send_methods = implode('||', $send_methods);
    $invite_sender = InviteSender::load($type);
    if (empty($invite_sender)) {
      $invite_sender = InviteSender::create(
        array(
          'id' => $type,
          'sending_methods' => $send_methods,
        )
      );
    }
    else {
      $invite_sender
        ->set('id', $type)
        ->set('sending_methods', $send_methods);
    }
    $invite_sender->save();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Assemble data.
    $data_value = $form_state->getValue('data');
    $data = !empty($data_value) ? $data_value : array();
    $data['target_role'] = $form_state->getValue('target_role');
    $form_state->setValue('data', serialize($data));
    parent::submitForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    // Add/update sending invite_sender.
    try {
      $this->updateInviteSender($form_state->getValue('send_method'), $entity);
    }
    catch (\Exception $e) {
      throw $e;
    }

    $status = $entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Invite type.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Invite type.', [
          '%label' => $entity->label(),
        ]));
    }
    // Reload blocks.
    $this->block_manager->clearCachedDefinitions();

    $form_state->setRedirect('entity.invite_type.collection', ['invite_type' => $entity->id()]);
  }


}
