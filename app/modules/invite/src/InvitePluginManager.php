<?php
/**
 * @file
 * Contains \Drupal\invite\InvitePluginManager.
 */

namespace Drupal\invite;


use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Extension\ModuleHandlerInterface;

class InvitePluginManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Invite', $namespaces, $module_handler, 'Drupal\invite\InvitePluginInterface', 'Drupal\Component\Annotation\Plugin');
    $this->alterInfo('invite_plugin_info');
    $this->setCacheBackend($cache_backend, 'invite_plugin_info');
  }

}