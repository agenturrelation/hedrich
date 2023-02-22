<?php

/**
* @file
* File upload handler class of IDENTICA Schadenform.
*/

require_once 'RandomStringGenerator.inc';

/**
 * Class SchadenFormFileUpload.
 */
class SchadenFormUploadFile {

  /**
   * The upload path.
   *
   * @var string
   */
  private $upload_path;

  /**
   * Getter of upload_path.
   *
   * @return string
   */
  public function getUploadPath() {
    return $this->upload_path;
  }

  /**
   * Setter of upload_path.
   *
   * @param string $upload_path
   */
  private function setUploadPath($upload_path) {
    $this->upload_path = $upload_path;
  }

  /**
   * Returns the document root of schadenform app.
   */
  private function getDocumentRoot() {
    // Remove 'app/SchadenForm/php/.
    $doc_root = substr(dirname(__FILE__), 0, -20);

    return $doc_root;
  }

  /**
   * The constructor.
   */
  public function __construct() {
    $this->setUploadPath($this->getDocumentRoot() . '/app/SchadenForm/upload');
  }

  /**
   * The destructor.
   */
  function __destruct() {
    $this->deleteUploadedFilesOlderThan();
  }

  /**
   * Generates and returns a unique file name.
   */
  private function getUniqueFileName($file_name) {

    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

    $generator = new RandomStringGenerator;

    return $generator->generate(36) . '.' . $file_extension;
  }

  /**
   * Renames and moves an uploaded file to upload directory.
   *
   * @param $file
   *   The files array from $_FILES for one single file.
   *
   *  @return array|bool
   *   The name of renamed file or FALSE if an error onccurs.
   */
  public function moveUploadedFileToUploadPath($file) {

    if (isset($file['tmp_name']) && file_exists($file['tmp_name']) && is_file($file['tmp_name'])) {

      $unique_filename = $this->getUniqueFileName($file["name"]);
      $target_file = $this->upload_path . '/' . $unique_filename;
      if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $unique_filename;
      }
    }

    return FALSE;
  }

  /**
   * Deletes an file from upload directory.
   *
   * @param $file_name
   *   The filename to delete.
   *
   * @return bool
   *   TRUE if file is deleted, othewise FALSE.
   */
  public function deleteUploadedFile($file_name) {

    $file_upload_path = $this->upload_path . '/' . $file_name;
    if (file_exists($file_upload_path) && is_file($file_upload_path)) {
      return unlink($file_upload_path);
    }

    return FALSE;
  }

  /**
   * Deletes files from upload directory which are older than x mintes.
   *
   * @param $minutes
   *  Files must be older that minutes to get delete.
   *  Default value = 1140 minutes = 1 day.
   */
  public function deleteUploadedFilesOlderThan($minutes = 1140) {
    $path = $this->upload_path;
    if ($handle = opendir($path)) {
      while (false !== ($file_name = readdir($handle))) {
        if (substr($file_name, 0, 1) != '.') {
          $date_last_modified = filemtime($path . '/' .$file_name);
          if((time() - $date_last_modified) > ($minutes * 60)) {
            $this->deleteUploadedFile($file_name);
          }
        }
      }
      closedir($handle);
    }
  }

}
