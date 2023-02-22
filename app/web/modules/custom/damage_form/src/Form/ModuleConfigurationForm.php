<?php

namespace Drupal\damage_form\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ModuleConfigurationForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'damage_form.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'damage_form_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config(static::SETTINGS);

    // Form settings:
    $form_settings = $config->get('form_settings');

    $form['form_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this
        ->t('Form Settings'),
      '#tree' => TRUE,
    );

    $form['form_settings']['has_mechanics'] = array(
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Has Mechanics'),
      '#description' => t('Check to display Mechanics section in damage form'),
      '#default_value' => $form_settings ? $form_settings['has_mechanics'] : 0,
    );

    $form['form_settings']['upload_path'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Image Upload Path'),
      '#description' => t('Use Drupal Uri schema: e.g. public://damage-form-upload'),
      '#default_value' => $form_settings ? $form_settings['upload_path'] : 'public://damage-form-upload',
    );

    // Email settings:
    $email_settings = $config->get('email_settings');

    /*
    echo "<pre>";
    print_r($email_settings);
    echo "</pre>";
    */

    $form['email_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this
        ->t('Email Settings'),
      '#tree' => TRUE,
    );

    $form['email_settings']['from_address'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('From email address'),
      '#description' => t('Enter the FROM email mail address. Note: Must be supported by your email SMTP account'),
      '#default_value' => $email_settings ? $email_settings['from_address'] : '',
    );

    $form['email_settings']['from_name'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('From name'),
      '#description' => t('Enter the FROM name.'),
      '#default_value' => $email_settings ? $email_settings['from_name'] : '',
    );

    $form['email_settings']['recipient_address'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Recipient email address'),
      '#description' => t('Enter the recipient email address for damage form requests'),
      '#default_value' => $email_settings ? $email_settings['recipient_address'] : '',
    );

    $form['email_settings']['recipient_address_bcc'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Recipient email address BCC'),
      '#description' => t('Enter an optional BCC recipient email address '),
      '#default_value' => $email_settings ? $email_settings['recipient_address_bcc'] : '',
    );

    // Request:
    $form['email_settings']['request'] = array(
      '#type' => 'fieldset',
      '#title' => $this
        ->t('General request email'),
      '#tree' => TRUE,
    );

    $form['email_settings']['request']['subject'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Email Subject'),
      '#description' => t('Email subject of general requests'),
      '#default_value' => $email_settings ? $email_settings['request']['subject'] : '',
    );

    $form['email_settings']['request']['body'] = array(
      '#type' => 'textarea',
      '#title' => $this
        ->t('Email Body'),
      '#description' => t('Email body of general requests. The variable {{USER_SUBMITTED_VALUES}} will be replaced with the entered values'),
      '#default_value' => $email_settings ? $email_settings['request']['body'] : '',
      '#rows' => 10,
      '#resizable' => TRUE,
    );

    // Damage:
    $form['email_settings']['damage'] = array(
      '#type' => 'fieldset',
      '#title' => $this
        ->t('Damage request'),
      '#tree' => TRUE,
    );

    $form['email_settings']['damage']['subject'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Email Subject'),
      '#description' => t('Email subject of damage requests'),
      '#default_value' => $email_settings ? $email_settings['damage']['subject'] : '',
    );

    $form['email_settings']['damage']['body'] = array(
      '#type' => 'textarea',
      '#title' => $this
        ->t('Email Body'),
      '#description' => t('Email body of damage requests. The variable {{USER_SUBMITTED_VALUES}} will be replaced with the entered values'),
      '#default_value' => $email_settings ? $email_settings['damage']['body'] : '',
      '#rows' => 10,
      '#resizable' => TRUE,
    );

    // Mechanic:
    $form['email_settings']['mechanic'] = array(
      '#type' => 'fieldset',
      '#title' => $this
        ->t('Mechanic request'),
      '#tree' => TRUE,
    );

    $form['email_settings']['mechanic']['subject'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Subject'),
      '#description' => t('Email subject of mechanic requests'),
      '#default_value' => $email_settings ? $email_settings['mechanic']['subject'] : '',
    );

    $form['email_settings']['mechanic']['body'] = array(
      '#type' => 'textarea',
      '#title' => $this
        ->t('Email Body'),
      '#description' => t('Email body of mechanic requests. The variable {{USER_SUBMITTED_VALUES}} will be replaced with the entered values'),
      '#default_value' => $email_settings ? $email_settings['mechanic']['body'] : '',
      '#rows' => 10,
      '#resizable' => TRUE,
    );

    // User confirmation:
    $form['email_settings']['user_confirmation'] = array(
      '#type' => 'fieldset',
      '#title' => $this
        ->t('User Confirmation'),
      '#tree' => TRUE,
    );

    $form['email_settings']['user_confirmation']['subject'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Email Subject'),
      '#description' => t('Subject of user confirmation email'),
      '#default_value' => $email_settings ? $email_settings['user_confirmation']['subject'] : '',
    );

    $form['email_settings']['user_confirmation']['body'] = array(
      '#type' => 'textarea',
      '#title' => $this
        ->t('Email Body'),
      '#description' => t('Body of user confirmation email.The variables {{WEBUSER.FIRSTNAME}} {{WEBUSER.LASTNAME}} will be replaced with the entered values'),
      '#default_value' => $email_settings ? $email_settings['user_confirmation']['body'] : '',
      '#rows' => 10,
      '#resizable' => TRUE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // dsm($form_state->getValue('form_settings'));

    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('form_settings', $form_state->getValue('form_settings'))
      ->set('email_settings', $form_state->getValue('email_settings'))
      ->save();

    parent::submitForm($form, $form_state);

    // Clear all caches.
    drupal_flush_all_caches();
  }
}
