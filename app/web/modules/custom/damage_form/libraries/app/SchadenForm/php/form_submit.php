<?php

/**
 * @file
 * Handles the form submit of IDENTICA Schadenform.
 */

header('Content-Type: charset=utf-8');

try {

  require_once 'SchadenFormSubmit.inc';

  // print_r($_REQUEST);

  if (!isset($_REQUEST['garage_ident']) || empty($_REQUEST['garage_ident'])) {
    // Check valid identifier.
    $obj_form = new SchadenFormSubmit(NULL, $_REQUEST);
    $ex_message = 'Missing garage identifier.';
    $obj_form->writeLogMessage('form_submit.php', $ex_message);
    throw new Exception($ex_message);
  }

  $garage_identifier = $_REQUEST['garage_ident'];
  $obj_form = new SchadenFormSubmit($garage_identifier, $_REQUEST);
  $obj_form->validateGarageIdentifier();
  $obj_form->sendEmail();
  $obj_form->sendEmailConfirmationToWebuser();

  print json_encode(array(
    'success' => 1,
  ));
}
catch (Exception $e) {

  //die($e->getMessage());

  print json_encode(array(
    'error' => 1,
  ));
}