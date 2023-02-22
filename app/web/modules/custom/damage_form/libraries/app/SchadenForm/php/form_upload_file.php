<?php

/**
 * @file
 * Rename and move an uploaded file from angular app to upload directory.
 */

require_once 'SchadenFormUploadFile.inc';

if (isset($_FILES['image_upload'])) {
  $obj = new SchadenFormUploadFile();
  $unique_filename = $obj->moveUploadedFileToUploadPath($_FILES['image_upload']);
  if($unique_filename) {
    $_FILES['image_upload']['tmp_name'] = $unique_filename;
    unset($_FILES['image_upload']['error']);
    unset($_FILES['image_upload']['size']);

    print json_encode(array(
      'success' => $_FILES['image_upload'],
    ));
  }
}