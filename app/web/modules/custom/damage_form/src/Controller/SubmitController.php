<?php
/**
 * @file
 * Contains \Drupal\damage_form\Controller\SubmitController.
 */
namespace Drupal\damage_form\Controller;

use Drupal\damage_form\FormSubmitHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

class SubmitController {
  public function content() {

    try {
      // echo "<pre>";
      $obj_form = new FormSubmitHandler($_REQUEST);
      $obj_form->sendEmail();
      $obj_form->sendEmailConfirmationToWebuser();

      return new JsonResponse([
        'success' => true,
      ]);
    } catch (\Exception $e) {
      return new JsonResponse([
        'success' => false,
      ]);
    }
  }
}
