<?php

namespace Drupal\leaflet_config\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\leaflet_config\Entity\MapBundleInterface;
use Drupal\leaflet_config\Entity\MapLayer;

/**
 * Class MapBundleForm.
 */
class MapBundleForm extends EntityForm {

  /**
   * Number of available layers.
   *
   * @var integer
   */
  protected $layerCount = 0;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $map_bundle = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $map_bundle->label(),
      '#description' => $this->t("Label for the Map bundle."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $map_bundle->id(),
      '#machine_name' => [
        'exists' => '\Drupal\leaflet_config\Entity\MapBundle::load',
      ],
      '#disabled' => !$map_bundle->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */
    $layers = $this->getData($map_bundle);

    usort($layers, [$this, 'sortByLayer']);

    $weight_delta = round($this->layerCount / 2);

    $regions = [
      'base_layers' => 'Base Layers',
      'overlay_layers' => 'Overlay Layers',
      'inactive' => 'Inactive',
    ];

    $form['layers'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Label'),
        $this->t('Module'),
        $this->t('Layer'),
        $this->t('Enabled'),
        $this->t('Primary'),
      ],
      '#attributes' => [
        'id' => 'layers',
      ],
    ];

    foreach ($layers as $layer) {
      $id = $layer['id'];
      $form['layers'][$id] = [
        'label' => [
          '#markup' => $layer['label'],
        ],
        'module_name' => [
          '#markup' => $layer['module'],
        ],
        'layer' => [
          '#markup' => $layer['layer_type'],
        ],
        'enabled' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable?'),
          '#title_display' => 'invisible',
          '#default_value' => $layer['enabled'],
        ],
        'default' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Default?'),
          '#title_display' => 'invisible',
          '#default_value' => $layer['default'],
        ],
        'structure' => [
          '#type' => 'value',
          '#value' => $layer['structure'],
        ],
      ];
    }

    $form['settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Settings'),
      '#description' => 'Configure how the map should be displayed',
      '#tree' => TRUE,
    ];

    $settings = $this->getSettingsKeys();

    foreach ($settings as $key => $label) {
      $form['settings'][$key] = [
        '#type' => 'checkbox',
        '#title' => $label,
        '#default_value' => $map_bundle->getSetting($key, FALSE),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $map_bundle = $this->entity;
    $map_bundle->removeInactiveLayers();

    $status = $map_bundle->save();

    \Drupal::cache()->invalidate('leaflet_map_info');

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Map bundle.', [
          '%label' => $map_bundle->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Map bundle.', [
          '%label' => $map_bundle->label(),
        ]));
    }
    $form_state->setRedirectUrl($map_bundle->toUrl('collection'));
  }

  /**
   * Prepare the layers for table.
   *
   * @param \Drupal\leaflet_config\Entity\MapBundleInterface $map_bundle
   *
   * @return array
   */
  public function getData(MapBundleInterface $map_bundle) {
    $invokes = \Drupal::moduleHandler()->getImplementations('leaflet_map_info');
    // $map_info = \Drupal::moduleHandler()->invokeAll('leaflet_map_info');
    $data = [];

    $existing_layers = $map_bundle->getLayers();
    foreach ($invokes as $invokee) {
      $map_info = \Drupal::moduleHandler()->invoke($invokee, 'leaflet_map_info');
      if (!$map_info) continue;

      foreach ($map_info as $key => $source) {
        foreach ($source['layers'] as $name => $layer) {
          $id = $this->machineName($key . '_' . $name);
          $layer_type = isset($layer['layer_type']) ? $layer['layer_type'] : 'base';

          $item = [
            'module' => $invokee,
            'label' => $name,
            'layer_id' => $name,
            'id' => $id,
            'weight' => 0, // isset($existing_layers[$id]) ? $existing_layers[$id]['weight'] :
            'layer_type' => $layer_type,
            'data' => json_encode($layer),
            'enabled' => isset($existing_layers[$id]) ? $existing_layers[$id]['enabled'] : FALSE,
            'default' => isset($existing_layers[$id]) ? $existing_layers[$id]['default'] : FALSE,
            'structure' => [
              'module' => $invokee,
              'map_bundle' => $key,
              'layer' => $name,
            ],
          ];
          $data[] = $item;
          $this->layerCount += 1;
        }
      }
    }

    $custom_layers = MapLayer::loadMultiple();
    foreach ($custom_layers as $layer) {
      $id = $this->machineName('leaflet_config_' . $layer->id());
      $layer_type = $layer->getSetting('layer_type', 'base');
      $item = [
        'module' => 'leaflet_config',
        'label' => $layer->label(),
        'id' => $id,
        'layer_id' => $layer->id(),
        'weight' => 0,
        'layer_type' => $layer_type,
        'data' => '',
        'enabled' => isset($existing_layers[$id]) ? $existing_layers[$id]['enabled'] : FALSE,
        'default' => isset($existing_layers[$id]) ? $existing_layers[$id]['default'] : FALSE,
        'structure' => [
          'module' => 'leaflet_config',
          'layer' => $layer->id(),
        ],
      ];

      $data[] = $item;
      $this->layerCount += 1;
    }

    return $data;
  }

  public function machineName($id) {
    $new_value = strtolower($id);
    $new_value = preg_replace('/[^a-z0-9_]+/', '_', $new_value);
    return preg_replace('/_+/', '_', $new_value);
  }

  public function getSettingsKeys() {
    return [
      'dragging' => $this->t('Dragging'),
      'touchZoom' => $this->t('Touch Zoom'),
      'scrollWheelZoom' => $this->t('Scroll Wheel Zoom'),
      'doubleClickZoom' => $this->t('Double Click Zoom'),
      'zoomControl' => $this->t('Zoom Control'),
      'attributionControl' => $this->t('Attribution Control'),
      'trackResize' => $this->t('Track Resize'),
      'fadeAnimation' => $this->t('Fade Animation'),
      'zoomAnimation' => $this->t('Zoom Animation'),
      'closePopupOnClick' => $this->t('Close Popup on Click'),
      'layerControl' => $this->t('Layer Control'),
    ];
  }

  public function sortByLayer($a, $b) {
    return strcmp($a['layer_type'], $b['layer_type']);
  }

}
