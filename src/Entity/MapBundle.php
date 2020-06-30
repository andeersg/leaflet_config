<?php

namespace Drupal\leaflet_config\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Map bundle entity.
 *
 * @ConfigEntityType(
 *   id = "map_bundle",
 *   label = @Translation("Map bundle"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\leaflet_config\MapBundleListBuilder",
 *     "form" = {
 *       "add" = "Drupal\leaflet_config\Form\MapBundleForm",
 *       "edit" = "Drupal\leaflet_config\Form\MapBundleForm",
 *       "delete" = "Drupal\leaflet_config\Form\MapBundleDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\leaflet_config\MapBundleHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "map_bundle",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/map_bundle/{map_bundle}",
 *     "add-form" = "/admin/structure/map_bundle/add",
 *     "edit-form" = "/admin/structure/map_bundle/{map_bundle}/edit",
 *     "delete-form" = "/admin/structure/map_bundle/{map_bundle}/delete",
 *     "collection" = "/admin/structure/map_bundle"
 *   }
 * )
 */
class MapBundle extends ConfigEntityBase implements MapBundleInterface {

  /**
   * The Map bundle ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Map bundle label.
   *
   * @var string
   */
  protected $label;

  /**
   * The layers.
   *
   * @var array
   */
  protected $layers = [];

  /**
   * Settings
   *
   * @var array
   */
  protected $settings = [];

  /**
   * {@inheritdoc}
   */
  public function getLayers() {
    return $this->layers;
  }

  /**
   * No need to keep store inactive layers.
   */
  public function removeInactiveLayers() {
    foreach ($this->layers as $key => $layer) {
      if (!$layer['enabled']) {
        unset($this->layers[$key]);
      }
    }
  }

  /**
   *
   */
  public function getSetting($key, $default = '') {
    if (isset($this->settings[$key])) {
      return $this->settings[$key];
    }
    return $default;
  }

  /**
   *
   */
  public function getSettings() {
    return $this->settings;
  }

}
