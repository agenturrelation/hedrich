<?php

/**
* @file
* Submit handler class of IDENTICA Schadenform.
*/

require_once 'libs/PHPMailer/PHPMailerAutoload.php';
require_once 'SchadenFormUploadFile.inc';

/**
 * Class SchadenFormSubmit.
 */
class SchadenFormSubmit {

  /**
   * The path to log file.
   * @var string
   */
  private $log_path;

  /**
   * The url of IDENTICA webservice.
   * @var string
   */
  // private $webservice_url = 'http://relation-projects.de/kleinschaden/ws/restapi/';
  private $webservice_url = 'https://projekte.relation-projects.de/identica/kleinschaden/ws/restapi/';

  /**
   * The garage identifier.
   * @var string.
   */
  private $garage_identifier;

  /**
   * The garage email address.
   * @var string.
   */
  private $garage_email_address;

  /**
   * The full data of garage.
   * @var string.
   */
  private $garage_data = array();

  /**
   * The form data.
   * @var string
   */
  private $form_data;

  /**
   * The class reference of SchadenFormUploadFile.
   * @var SchadenFormUploadFile
   */
  private $schadenFormUploadFile;

  /**
   * The class reference of PHPMailer.
   *
   * @var PHPMailer
   */
  private $phpMailer;

  /**
   * Returns the document root of schadenform app.
   */
  private function getDocumentRoot() {
    // Remove 'app/SchadenForm/php/.
    $doc_root = substr(dirname(__FILE__), 0, -20);

    return $doc_root;
  }

  /**
   * Setter of $log_path.
   */
  public function setLogPath() {
    $this->log_path = $this->getDocumentRoot() . '/app/SchadenForm/log';
  }

  /**
   * @param string $garage_identifier
   */
  public function setGarageIdentifier($garage_identifier) {
    $this->garage_identifier = $garage_identifier;
  }

  /**
   * Setter of $form_data.
   *
   * @param array $form_data
   */
  public function setFormData($form_data) {

    // Correct the form data array, must be run tru json_decode().
    foreach($form_data as $key => $value) {
      if ($key == 'formType') {
        $this->form_data[$key] = $value;
      }
      else {
        $this->form_data[$key] = json_decode($value);
      }
    }
  }

  /**
   * Setter of $schadenFormUploadFile.
   */
  public function setSchadenFormUploadFile() {
    $this->schadenFormUploadFile = new SchadenFormUploadFile();
  }

  /**
   * Setter of phpMailer.
   */
  public function setPhpMailer() {
    $this->phpMailer = new PHPMailer();
    $this->phpMailer->CharSet = 'UTF-8';
  }

  /**
   * Writes a message to log file.
   * @param $method
   * @param $message
   * @param string $seperator
   *
   * @throws \Exception
   */
  public function writeLogMessage($method, $message, $seperator = "\t") {

    $log_message = "\n";
    $log_message .= date('d.m.Y H:i:s') . $seperator;
    $log_message .= $_SERVER['REMOTE_ADDR'] . $seperator;
    $log_message .= $this->garage_identifier . $seperator;
    $log_message .= str_replace('SchadenFormSubmit', '', $method) . $seperator;
    $log_message .= $message;

    $file_name = date('Y-m-d') . '.log';

    echo $this->log_path . '/' . $file_name;
    echo "message" . $log_message;
    if(!file_put_contents($this->log_path . '/' . $file_name, $log_message, FILE_APPEND)) {
      $ex_message = 'Error writing to log file.';
      throw new Exception($ex_message);
    }
  }

  /**
   * The constructor.
   */
  public function __construct($garage_identifier, $form_data) {

    $this->setLogPath();
    $this->setGarageIdentifier($garage_identifier);
    $this->setFormData($form_data);
    $this->setSchadenFormUploadFile();
  }

  /**
   * Validates the garage identifier and stores garage data.
   *
   * Sets $this->garage_email_address and $this->garage_data with data from
   * webservice on succesfull validation.
   */
  public function validateGarageIdentifier() {

    // cURL init.
    $full_webservice_url = $this->webservice_url . 'identica_garage/' . $this->garage_identifier;
    $curl = curl_init($full_webservice_url);

    // cURL options.
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FAILONERROR, FALSE);
    curl_setopt($curl, CURLOPT_HEADER, TRUE);

    $response = curl_exec($curl);
    // print_r($response);

    // Then, after your curl_exec call:
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $body_json = json_decode($body);
    curl_close($curl);

    if ($http_code != 200) {
      $ex_message = 'Error on validation webservice, status code: ' . $http_code;
      $ex_message .= ', Body: '. $body;

      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new Exception($ex_message);
    }

    if (!isset($body_json->email) || empty($body_json->email)) {
      $ex_message = 'Invalid garage identifier, not found at identica webservice.';
      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new Exception($ex_message);
    }

    // Set $garage_data
    $this->garage_data = $body_json;

    // Set garage_email_address.
    $this->garage_email_address = $body_json->email;

  }


  /**
   * Gets a humanreadable name for formDate keys.
   *
   * @param $key_name
   *  The keyname of $this->formData[group][key].
   * @return string
   *   The human readable name for the given key anme.
   */
  private function getReadableNameFormdateKeys($key_name) {

    switch($key_name) {
      case 'firstname':
        return 'Vorname';

      case 'lastname':
        return 'Nachname';

      case 'street':
        return 'Straße, Nr.';

      case 'postalcode':
        return 'PLZ';

      case 'city':
        return 'Ort';

      case 'phone':
        return 'Telefon';

      case 'email':
        return 'E-Mail';

      case 'service':
        return 'Wie kommt Ihr Fahrzeug in unsere Werkstatt?';

      case 'manufacturer':
        return 'Hersteller';

      case 'model':
        return 'Modell';

      case 'registrationDate':
        return 'Erstzulassung';

      case 'mileage':
        return 'Kilometerstand';

      case 'modelCode2':
        return 'Schlüsselnummer 2';

      case 'modelCode3':
        return 'Schlüsselnummer 3';

      case 'vehicleIdentNo':
        return 'Fahrgestellnummer';

      default:
        return $key_name;
    }
  }

  /**
   * Returns a formatted single line of form data value.
   *
   * Used for email body.
   *
   * @param string $key_name
   *  The keyname of $this->formData[group][key].
   *
   * @param string $value
   *  The value of this key.
   *
   * @param bool $new_line
   *   The flag to add new line.
   *
   * @return string
   *   The formatted line of a single form data value.
   */
  private function getUserValueEmailBodyLine($key_name, $value, $new_line = TRUE) {

    $line = $this->getReadableNameFormdateKeys($key_name) . ': ';
    if (empty($value)) {
      $line .= '-';
    }
    else {

      if ($key_name == 'service') {
        // Exception for service key.
        if ($value == 'self') {
          $line .= 'Ich komme zu Ihnen';
        }
        else {
          $line .= 'IDENTICA Hol,- und Bringservice';
        }
      }
      else {
        $line .= $value;
      }
    }

    if ($new_line) {
      $line .= "\n";
    }

    return $line;
  }

  /**
   * Send the email to the $this->garage_email_address.
   */
  public function sendEmail() {

    // (Re)set PhpMailer.
    $this->setPhpMailer();

    // Check that email address is avail.
    if (empty($this->garage_email_address)) {
      $ex_message = 'Error on sending email: Email address of recepient is missing';
      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new Exception($ex_message);
    }

    $this->phpMailer->From = 'info@identica.de';
    $this->phpMailer->FromName = 'IDENTICA Schadenformular';
    $this->phpMailer->addAddress($this->garage_email_address);
    $this->phpMailer->addBCC('schadenmelden@identica.de');

    $this->phpMailer->isHTML(false);

    // Subject by form type.
    $this->phpMailer->Subject = 'IDENTICA Onlineformular: ';
    if ($this->form_data['formType'] == 'request') {
      $this->phpMailer->Subject .= 'Allgemeine Anfrage';
    }
    else if ($this->form_data['formType'] == 'damage' && $this->form_data['damage']->damageFormType == 'body-paint') {
      $this->phpMailer->Subject .= 'Karosserie & Lack Schaden';
    }
    else {
      $this->phpMailer->Subject .= 'Mechanik Anfrage';
    }

    // Load body from template.
    $template_path = $this->getDocumentRoot() . '/app/SchadenForm/email_template.txt';
    $body = file_get_contents($template_path);
    if (!$body) {
      $ex_message = 'Error on loading email template: ' . $template_path;
      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new Exception($ex_message);
    }

    // Replacement of {{USER_SUBMITTED_VALUES}}
    // Fill with data from form data.
    $user_submitted_values = "";
    if ($this->form_data['formType'] == 'damage') {

      if ($this->form_data['damage']->damageFormType == 'body-paint') {
        // Form type 'damage' / 'body-paint':
        $user_submitted_values .= 'Formulartyp: Karosserie & Lack Schaden' . "\n";
        $user_submitted_values .= '---------------------------------------------' . "\n";

        // Car parts
        if (isset($this->form_data['damage']->carParts) && is_object($this->form_data['damage']->carParts) && count($this->form_data['damage']->carParts) > 0) {
          $user_submitted_values .= "\n" . 'Beschädigte Fahrzeugteile:' . "\n";
          foreach($this->form_data['damage']->carParts as $part_id => $part_name) {
            $user_submitted_values .= '- ' . $part_name . ' (' . $part_id .')' . "\n";
          }
        }
      }
      elseif ($this->form_data['damage']->damageFormType == 'mechanics') {
        // Form type 'damage' / 'mechanics':
        $user_submitted_values .= 'Formulartyp: Mechanik Anfrage' . "\n";
        $user_submitted_values .= '---------------------------------------------' . "\n";
      }

      // Car specs
      if (isset($this->form_data['damage']->carSpecs) && is_object($this->form_data['damage']->carSpecs) && count($this->form_data['damage']->carSpecs) > 0) {
        $user_submitted_values .= "\n" . 'Angaben zum Fahrzeug:' . "\n";
        foreach($this->form_data['damage']->carSpecs as $key => $value) {
          $user_submitted_values .= $this->getUserValueEmailBodyLine($key, $value);
        }
      }

      // Service.
      $user_submitted_values .= "\n";
      $user_submitted_values .= $this->getUserValueEmailBodyLine('service', $this->form_data['damage']->service);
    }

    /*
    if ($this->form_data['formType'] == 'request') {
      // Form type 'request':
      $user_submitted_values .= 'Formulartyp: Anfrageformular' . "\n";
      $user_submitted_values .= '---------------------------------------------' . "\n";
    }
    */

    // Address data.
    $user_submitted_values .= "\n" . 'Adressdaten:' . "\n";
    $user_submitted_values .= '---------------------------------------------' . "\n";
    foreach($this->form_data['contactData'] as $key => $value) {
      $user_submitted_values .= $this->getUserValueEmailBodyLine($key, $value);
    }

    // Message.
    if(isset($this->form_data['request']->message)) {
      $user_submitted_values .= "\n" . 'Nachricht' . "\n";
      $user_submitted_values .= '---------------------------------------------' . "\n";
      $user_submitted_values .= $this->form_data['request']->message . "\n";
    }

    // Replace submitted values in mail body text.
    $body = str_replace('{{USER_SUBMITTED_VALUES}}', $user_submitted_values, $body);

    // echo '<pre>' . $body;
    // return;

    // Set body.
    $this->phpMailer->Body = $body;

    // File attachments.
    $attachments = array();
    if (isset($this->form_data['fileUploads']) && is_object($this->form_data['fileUploads'])) {
      $file_upload_path = $this->schadenFormUploadFile->getUploadPath();
      foreach($this->form_data['fileUploads'] as $file) {
        // Check that file exists still in upload directory.
        $upload_file_name = $file_upload_path . '/' . $file->tmp_name;
        if (file_exists($upload_file_name) && is_file($upload_file_name)) {
          $attachments[] = $file;
        }
      }
      // Add attachment to PHPMailer.
      foreach($attachments as $attachment) {
        $this->phpMailer->addAttachment($file_upload_path . '/' . $attachment->tmp_name , $attachment->name);    // Optional name
      }
    }

    // Send Mail.
    if(!$this->phpMailer->send()) {
      $ex_message = 'Error on sending email:' . $this->phpMailer->ErrorInfo;
      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new Exception($ex_message);
    }

    // Delete attachment files after sending mails.
    foreach($attachments as $attachment) {
      $this->schadenFormUploadFile->deleteUploadedFile($attachment->tmp_name);
    }
  }


  /**
   * Send the email to the $this->garage_email_address.
   */
  public function sendEmailConfirmationToWebuser() {

    // (Re)set PhpMailer.
    $this->setPhpMailer();

    // Check that email address is avail.
    if (empty($this->form_data['contactData']->email)) {
      $ex_message = 'Error on sending email: Email address of recepient is missing';
      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new Exception($ex_message);
    }

    $this->phpMailer->From = $this->garage_data->email;
    $this->phpMailer->FromName = $this->garage_data->company_name;
    $this->phpMailer->addAddress($this->form_data['contactData']->email);
    $this->phpMailer->isHTML(false);

    // Subject by form type.
    $this->phpMailer->Subject = 'Ihre Online-Anfrage an IDENTICA';

    // Load body from template.
    $template_path = $this->getDocumentRoot() . '/app/SchadenForm/email_template.confirmation.webuser.txt';
    $body = file_get_contents($template_path);
    if (!$body) {
      $ex_message = 'Error on loading email template: ' . $template_path;
      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new Exception($ex_message);
    }

    // Replacements Webuser.
    $body = str_replace('{{WEBUSER.FIRSTNAME}}', $this->form_data['contactData']->firstname, $body);
    $body = str_replace('{{WEBUSER.LASTNAME}}', $this->form_data['contactData']->lastname, $body);

    // Replacements Garagedata.
    $body = str_replace('{{GARAGEDATA.COMPANY_NAME}}', $this->garage_data->company_name, $body);
    $body = str_replace('{{GARAGEDATA.COMPANY_NAME_2}}', $this->garage_data->company_name_2, $body);
    $body = str_replace('{{GARAGEDATA.PRIMARY_ADDRESS_STREET}}', $this->garage_data->primary_address_street, $body);
    $body = str_replace('{{GARAGEDATA.PRIMARY_ADDRESS_POSTALCODE}}', $this->garage_data->primary_address_postalcode, $body);
    $body = str_replace('{{GARAGEDATA.PRIMARY_ADDRESS_CITY}}', $this->garage_data->primary_address_city, $body);
    $body = str_replace('{{GARAGEDATA.PHONE_OFFICE}}', $this->garage_data->phone_office, $body);
    $body = str_replace('{{GARAGEDATA.PHONE_FAX}}', $this->garage_data->phone_fax, $body);
    $body = str_replace('{{GARAGEDATA.EMAIL}}', $this->garage_data->email, $body);
    $body = str_replace('{{GARAGEDATA.WEB_URL}}', $this->garage_data->web_url, $body);

    // echo '<pre>' . $body;
    // return;

    // Set body.
    $this->phpMailer->Body = $body;

    // Send Mail.
    if(!$this->phpMailer->send()) {
      $ex_message = 'Error on sending email:' . $this->phpMailer->ErrorInfo;
      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new Exception($ex_message);
    }
  }

  /**
   * The destructor.
   */
  function __destruct() {

    $this->schadenFormUploadFile->deleteUploadedFilesOlderThan();
  }

}
