<?php

namespace Drupal\leaflet_config\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Map layer entity.
 *
 * @ConfigEntityType(
 *   id = "map_layer",
 *   label = @Translation("Map layer"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\leaflet_config\MapLayerListBuilder",
 *     "form" = {
 *       "add" = "Drupal\leaflet_config\Form\MapLayerForm",
 *       "edit" = "Drupal\leaflet_config\Form\MapLayerForm",
 *       "delete" = "Drupal\leaflet_config\Form\MapLayerDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\leaflet_config\MapLayerHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "map_layer",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/map_layer/{map_layer}",
 *     "add-form" = "/admin/structure/map_layer/add",
 *     "edit-form" = "/admin/structure/map_layer/{map_layer}/edit",
 *     "delete-form" = "/admin/structure/map_layer/{map_layer}/delete",
 *     "collection" = "/admin/structure/map_layer"
 *   }
 * )
 */
class MapLayer extends ConfigEntityBase implements MapLayerInterface {

  /**
   * The Map layer ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Map layer label.
   *
   * @var string
   */
  protected $label;

  /**
   * The description.
   *
   * @var string
   */
  protected $description;

  /**
   * Additional settings.
   *
   * @var array
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrlTemplate() {
    return $this->settings['urlTemplate'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAttribution() {
    return $this->settings['attribution'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting($key, $default = '') {
    if (isset($this->settings[$key])) {
      return $this->settings[$key];
    }
    return $default;
  }
}
