<?php
/**
 * @file
 * Contains \Drupal\damage_form\Controller\UploadImageController.
 */
namespace Drupal\damage_form\Controller;

use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class UploadImageController {

  public function content() {

    // Get damage form settings.
    $config = \Drupal::config('damage_form.settings');
    $form_settings = $config->get('form_settings');

    if (isset($_FILES['image_upload'])) {

      $source_file = $_FILES['image_upload']['tmp_name'];
      $dest_file = $form_settings['upload_path'] . '/' . $_FILES['image_upload']['name'];

      if (\Drupal::service('file_system')->prepareDirectory($form_settings['upload_path'], FileSystemInterface::CREATE_DIRECTORY)) {
        // Copy uploaded file to destination folder.
        $destination_uri = \Drupal::service('file_system')->copy($source_file, $dest_file, FileSystemInterface::EXISTS_RENAME);
        if ($destination_uri) {

          // Delete uploaded file in servers 'tmp' directory.
          $this->deleteTmpFile($_FILES['image_upload']['tmp_name']);

          $_FILES['image_upload']['tmp_name'] = $destination_uri;
          unset($_FILES['image_upload']['error']);
          unset($_FILES['image_upload']['size']);

          return new JsonResponse([
            'success' => true,
            'data' => $_FILES['image_upload'],
            'method' => 'GET',
          ]);
        }
      }
    }

    return new JsonResponse([
      'success' => false,
      'method' => 'GET',
    ]);
  }

  /**
   * Delete uploaded file in servers 'tmp' directory.
   */
  private function deleteTmpFile($file_path) {
    if (file_exists($file_path) && is_file($file_path)) {
      return unlink($file_path);
    }
  }
}
