<?php

namespace Drupal\social_media_links;

class IconsetFinderService {

  protected $installDirs = array();
  protected $searchDirs = array();
  protected $iconsets = array();

  public function __construct() {
    $this->setSearchDirs();
    $this->setIconsets();
  }

  public function getPath($id) {
    return isset($this->iconsets[$id]) ? $this->iconsets[$id] : null;
  }

  public function getSearchDirs() {
    return $this->searchDirs;
  }

  /**
   * Defines a list of the locations, where icon sets are searched.
   *
   * @return array
   */
  protected function setSearchDirs() {
    // Similar to 'modules' and 'themes' directories inside an installation
    // profile, installation profiles may want to place libraries into a
    // 'libraries' directory.
    $profile = drupal_get_profile();
    if ($profile && strpos($profile, "core") === FALSE) {
      $profile_path = drupal_get_path('profile', $profile);
      $searchdirs[] = "$profile_path/libraries";
    }

    // Search sites/all/libraries for backwars-compatibility.
    $searchdirs[] = 'sites/all/libraries';

    // Always search the root 'libraries' directory.
    $searchdirs[] = 'libraries';

    // Also search sites/<domain>/*.
    $site_path = \Drupal::service('kernel')->getSitePath();
    $searchdirs[] = "$site_path/libraries";

    // Add the social_media_links module directory.
    $searchdirs[] = drupal_get_path('module', 'social_media_links') . '/libraries';

    $this->searchDirs = $searchdirs;
  }

  public function getInstallDirs() {
    if (empty($this->installDirs)) {
      $this->setInstallDirs();
    }

    return $this->installDirs;
  }

  protected function setInstallDirs() {
    $searchdirs = $this->searchDirs;

    // Remove the core and social_media_links module directory from the possible
    // target directories for installation.
    foreach ($searchdirs as $key => $dir) {
      if (preg_match("/core|social_media_links/", $dir)) {
        unset($searchdirs[$key]);
      }
    }

    $this->installDirs = $searchdirs;
  }

  public function getIconsets() {
    return $this->iconsets;
  }

  /**
   * Searches the directories for libraries (e.g. Icon Sets).
   *
   * Returns an array of library directories from the all-sites directory
   * (i.e. sites/all/libraries/), the profiles directory, and site-specific
   * directory (i.e. sites/somesite/libraries/). The returned array will be keyed
   * by the library name. Site-specific libraries are prioritized over libraries
   * in the default directories. That is, if a library with the same name appears
   * in both the site-wide directory and site-specific directory, only the
   * site-specific version will be listed.
   *
   * Most of the code in this function are borrowed from the libraries module
   * (http://drupal.org/project/libraries).
   */
  protected function setIconsets() {
    // Retrieve a list of directories.
    $directories = array();
    $nomask = array('CVS');

    foreach ($this->searchDirs as $dir) {
      if (is_dir($dir) && $handle = opendir($dir)) {
        while (FALSE !== ($file = readdir($handle))) {
          if (!in_array($file, $nomask) && $file[0] != '.') {
            if (is_dir("$dir/$file")) {
              $directories[$file] = "$dir/$file";
            }
          }
        }
        closedir($handle);
      }
    }

    $this->iconsets = (array) $directories;
  }

}
