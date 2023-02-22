<?php

namespace Drupal\damage_form\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Damage Form' Block.
 *
 * @Block(
 *   id = "damage_form_block",
 *   admin_label = @Translation("Damage Form Output"),
 *   category = @Translation("Damage Form"),
 * )
 */
class DamageFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get damage form settings.
    $config = \Drupal::config('damage_form.settings');
    $form_settings = $config->get('form_settings');

    // Get host and module path.
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('damage_form')->getPath();
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $base_path = \Drupal::request()->getBasePath();

    $block_data = [
      "module_path" =>  $host . $base_path . '/'. $module_path,
      "base_url" =>  $host . $base_path,
      "has_mechanics" => $form_settings['has_mechanics'] ? 1: 0
    ];

    return [
      '#theme' => 'damage_form_block',
      '#data' => $block_data,
      '#attached' => [
        'library' => [
          'damage_form/app',
        ],
      ],
    ];
  }

}
