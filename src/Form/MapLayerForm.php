<?php

namespace Drupal\leaflet_config\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MapLayerForm.
 */
class MapLayerForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $map_layer = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $map_layer->label(),
      '#description' => $this->t("Label for the Map layer."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $map_layer->id(),
      '#machine_name' => [
        'exists' => '\Drupal\leaflet_config\Entity\MapLayer::load',
      ],
      '#disabled' => !$map_layer->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */
    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#maxlength' => 255,
      '#default_value' => $map_layer->getDescription(),
    ];

    $form['settings'] = [
      '#tree' => TRUE,
    ];

    $form['settings']['urlTemplate'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL Template'),
      '#maxlength' => 255,
      '#default_value' => $map_layer->getUrlTemplate(),
      '#description' => $this->t("Url for tiles, typically with x, y, and z parameters."),
      '#required' => TRUE,
    ];

    $form['settings']['attribution'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Attribution'),
      '#maxlength' => 255,
      '#default_value' => $map_layer->getAttribution(),
      '#description' => $this->t("Most map layers require attribution."),
      '#required' => FALSE,
    ];

    $form['settings']['layer_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Layer Type'),
      '#default_value' => $map_layer->getSetting('layer_type', 'base_layer'),
      '#options' => [
        'base' => 'Base Layer',
        'overlay' => 'Overlay Layer',
      ],
      '#required' => TRUE,
    ];

    $form['settings']['minZoom'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum zoom'),
      '#default_value' => $map_layer->getSetting('minZoom', 0),
    ];
    $form['settings']['maxZoom'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum zoom'),
      '#default_value' => $map_layer->getSetting('maxZoom', 0),
    ];

    $form['settings']['subdomains'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subdomains'),
      '#default_value' => $map_layer->getSetting('subdomains', ''),
      '#description' => $this->t('Comma separated list of subdomains(e.g. "mt1, mt2, mt3").'),
    ];

    $form['settings']['errorTileUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Tile URL'),
      '#default_value' => $map_layer->getSetting('errorTileUrl', ''),
    ];

    $form['settings']['zoomOffset'] = [
      '#type' => 'number',
      '#title' => $this->t('Zoom Offset'),
      '#default_value' => $map_layer->getSetting('zoomOffset', 0),
    ];

    $form['settings']['tms'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('TMS'),
      '#default_value' => $map_layer->getSetting('tms', FALSE),
    ];

    $form['settings']['zoomReverse'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reverse Zoom'),
      '#default_value' => $map_layer->getSetting('zoomReverse', FALSE),
    ];

    $form['settings']['detectRetina'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Detect Retina'),
      '#default_value' => $map_layer->getSetting('detectRetina', FALSE),
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $map_layer = $this->entity;

    $status = $map_layer->save();

    \Drupal::cache()->invalidate('leaflet_map_info');

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Map layer.', [
          '%label' => $map_layer->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Map layer.', [
          '%label' => $map_layer->label(),
        ]));
    }
    $form_state->setRedirectUrl($map_layer->toUrl('collection'));
  }

}
