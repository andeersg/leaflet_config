<?php

namespace Drupal\leaflet_config\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Map layer entities.
 */
interface MapLayerInterface extends ConfigEntityInterface {

  /**
   * Gets the description.
   *
   * @return string
   *   The description.
   */
  public function getDescription();

  /**
   * Gets the map url.
   *
   * @return string
   *   The url template.
   */
  public function getUrlTemplate();

  /**
   * Get the attribution value.
   *
   * @return mixed
   */
  public function getAttribution();

  /**
   * Get a settings field.
   *
   * @param string $key
   *   A settings key.
   * @param string $default
   *   The default value.
   *
   * @return mixed
   */
  public function getSetting($key, $default);
}
