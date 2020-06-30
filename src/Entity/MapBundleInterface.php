<?php

namespace Drupal\leaflet_config\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Map bundle entities.
 */
interface MapBundleInterface extends ConfigEntityInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Return the array of layers.
   *
   * @return array
   */
  public function getLayers();

}
