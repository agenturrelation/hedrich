<?php

namespace Drupal\damage_form;

use Drupal\Core\Messenger\MessengerInterface;

class FormSubmitHandler {

  /**
   * The email settings array.
   * @var array
   */
  private $email_settings = [];

  /**
   * The form settings array.
   * @var array
   */
  private $form_settings = [];

  /**
   * The form data.
   * @var array
   */
  private $form_data = [];

  /**
   * Setter of email and form settings.
   */
  public function setSettings() {
    // Get damage form settings.
    $config = \Drupal::config('damage_form.settings');
    // Set settings.
    $this->form_settings = $config->get('form_settings');
    $this->email_settings = $config->get('email_settings');
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
   * Writes a message to log file.
   * @param $method
   * @param $message
   * @param string $seperator
   *
   * @throws \Exception
   */
  public function writeLogMessage($method, $message, $seperator = "\t") {

    /*
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
    */
  }

  /**
   * The constructor.
   */
  public function __construct($form_data) {
    // $this->setLogPath();
    // $this->setGarageIdentifier($garage_identifier);
    $this->setSettings();
    $this->setFormData($form_data);

    // print_r($this->form_data);
    // $this->setSchadenFormUploadFile();
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

    // Set subject and body depending on form type.
    if ($this->form_data['formType'] == 'request') {
      $params['subject'] = $this->email_settings['request']['subject'];
      $params['body'] = $this->email_settings['request']['body'];
    }
    else if ($this->form_data['formType'] == 'damage' && $this->form_data['damage']->damageFormType == 'body-paint') {
      $params['subject'] = $this->email_settings['damage']['subject'];
      $params['body'] = $this->email_settings['damage']['body'];
    }
    else {
      $params['subject'] = $this->email_settings['mechanic']['subject'];
      $params['body'] = $this->email_settings['mechanic']['body'];
    }

    /*
    // Set headers.
    $params['headers'] = [
      "Return-Path" => $this->email_settings['from_address'],
      "Sender" => $this->email_settings['from_address'],
      "From" => '"' . $this->email_settings['from_name'] . '" <' . $this->email_settings['from_address'] . '>',
    ];
    */

    // Set form name (supported by SMTP module).
    if ($this->email_settings['from_name']) {
      $params['from_name'] = $this->email_settings['from_name'];
    }

    // Set form email (supported by SMTP module).
    if ($this->email_settings['from_address']) {
      $params['from_mail'] = $this->email_settings['from_address'];
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
        if (isset($this->form_data['damage']->carParts) && is_object($this->form_data['damage']->carParts)) {
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
      if (isset($this->form_data['damage']->carSpecs) && is_object($this->form_data['damage']->carSpecs)) {
        $user_submitted_values .= "\n" . 'Angaben zum Fahrzeug:' . "\n";
        foreach($this->form_data['damage']->carSpecs as $key => $value) {
          $user_submitted_values .= $this->getUserValueEmailBodyLine($key, $value);
        }
      }

      // Service.
      $user_submitted_values .= "\n";
      $user_submitted_values .= $this->getUserValueEmailBodyLine('service', $this->form_data['damage']->service);
    }

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

    // Replace submitted values in mail body.
    $params['body'] = str_replace('{{USER_SUBMITTED_VALUES}}', $user_submitted_values, $params['body']);

    // File attachments.
    $params['attachments'] = array();
    if (isset($this->form_data['fileUploads']) && is_object($this->form_data['fileUploads'])) {
      foreach($this->form_data['fileUploads'] as $file) {
        // Check that file exists still in upload directory.
        // $upload_file_name = $file_upload_path . '/' . $file->tmp_name;
        if (file_exists($file->tmp_name) && is_file($file->tmp_name)) {
          $params['attachments'][] = array(
            'filepath' => \Drupal::service('file_system')->realpath($file->tmp_name),
            'filename' =>  $file->name,
            'filemime' => $file->type,
          );
        }
      }
    }

    // Send Mail:
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'damage_form';
    $email_identifier = 'damage_form_submission';

    // Set recipient address.
    $to = $this->email_settings['recipient_address'];

    $result = $mailManager->mail($module, $email_identifier, $to, \Drupal::currentUser()->getPreferredLangcode(), $params, NULL, true);
    // print_r($result);

    if (empty($result) || empty($result['send']) || $result['send'] === FALSE) {
      // Set error log message.
      throw new \Exception($this->setErrorLogMessage($to));
    }

    // Set success log message.
    $this->setSuccessLogMessage($to);
  }

  /**
   * Send the email to the $this->garage_email_address.
   */
  public function sendEmailConfirmationToWebuser() {

    // Check that email address is avail.
    if (empty($this->form_data['contactData']->email)) {
      $ex_message = 'Error on sending email: Email address of recipient is missing';
      $this->writeLogMessage(__METHOD__, $ex_message);
      throw new \Exception($ex_message);
    }

    /*
    // Set headers.
    $params['headers'] = [
      "Return-Path" => $this->email_settings['from_address'],
      "Sender" => $this->email_settings['from_address'],
      "From" => '"' . $this->email_settings['from_name'] . '" <' . $this->email_settings['from_address'] . '>',
    ];
    */

    // Set form name (supported by SMTP module).
    if ($this->email_settings['from_name']) {
      $params['from_name'] = $this->email_settings['from_name'];
    }

    // Set form email (supported by SMTP module).
    if ($this->email_settings['from_address']) {
      $params['from_mail'] = $this->email_settings['from_address'];
    }

    // Set subject and body.
    $params['subject'] = $this->email_settings['user_confirmation']['subject'];
    $params['body'] = $this->email_settings['user_confirmation']['body'];

    // Replacements Webuser.
    $params['body'] = str_replace('{{WEBUSER.FIRSTNAME}}', $this->form_data['contactData']->firstname, $params['body']);
    $params['body'] = str_replace('{{WEBUSER.LASTNAME}}', $this->form_data['contactData']->lastname, $params['body']);

    // Send Mail:
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'damage_form';
    $email_identifier = 'damage_form_submission';

    // Set recipient address.
    $to = $this->email_settings['recipient_address'];

    $result = $mailManager->mail($module, $email_identifier, $to, \Drupal::currentUser()->getPreferredLangcode(), $params, NULL, true);
    // print_r($result);

    if (empty($result) || empty($result['send']) || $result['send'] === FALSE) {
      // Set error log message.
      throw new \Exception($this->setErrorLogMessage($to));
    }

    // Set success log message.
    $this->setSuccessLogMessage($to);
  }

  /**
   * Set success log message.
   *
   * @return string
   */
  private function setSuccessLogMessage($to) {
    $message = t('An email notification has been sent to @email ', array('@email' => $to));
    \Drupal::logger('damage-form-mail-log')->notice($message);

    return $message;
  }

  /**
   * Set error log message.
   *
   * @return string
   */
  private function setErrorLogMessage($to) {
    $message = t('There was a problem sending damage form email to: @email.', array('@email' => $to));
    \Drupal::logger('damage-form-mail-log')->error($message);

    return $message;
  }

}
