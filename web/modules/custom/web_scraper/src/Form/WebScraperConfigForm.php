<?php

namespace Drupal\web_scraper\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configuration form for Web Scraper module.
 */
class WebScraperConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['web_scraper.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'web_scraper_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('web_scraper.settings');
    
    // Get current URL configurations
    $urls = $config->get('urls') ?: [];
    
    $form['urls_container'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#prefix' => '<div id="urls-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    
    // Form state determines how many URL fields to show
    $url_count = $form_state->get('url_count');
    if ($url_count === NULL) {
      $url_count = count($urls) > 0 ? count($urls) : 1;
      $form_state->set('url_count', $url_count);
    }
    
    // Add URL form fields
    for ($i = 0; $i < $url_count; $i++) {
      $form['urls_container']['url'][$i] = [
        '#type' => 'details',
        '#title' => $this->t('URL Configuration @number', ['@number' => $i + 1]),
        '#open' => TRUE,
      ];
      
      $form['urls_container']['url'][$i]['url'] = [
        '#type' => 'url',
        '#title' => $this->t('URL'),
        '#description' => $this->t('Enter the URL of the website to scrape.'),
        '#default_value' => isset($urls[$i]['url']) ? $urls[$i]['url'] : '',
        '#required' => $i == 0,
      ];
      
      $form['urls_container']['url'][$i]['ssl_username'] = [
        '#type' => 'textfield',
        '#title' => $this->t('SSL Username (if applicable)'),
        '#description' => $this->t('Enter the SSL username if required for accessing the URL.'),
        '#default_value' => isset($urls[$i]['ssl_username']) ? $urls[$i]['ssl_username'] : '',
      ];
      
      $form['urls_container']['url'][$i]['ssl_password'] = [
        '#type' => 'password',
        '#title' => $this->t('SSL Password (if applicable)'),
        '#description' => $this->t('Enter the SSL password if required for accessing the URL.'),
        '#attributes' => ['autocomplete' => 'off'],
      ];
      
      if (isset($urls[$i]['ssl_password']) && !empty($urls[$i]['ssl_password'])) {
        $form['urls_container']['url'][$i]['ssl_password_exists'] = [
          '#type' => 'markup',
          '#markup' => '<div>' . $this->t('A password is already set. Leave blank to keep the current password.') . '</div>',
        ];
      }
      
      $form['urls_container']['url'][$i]['selectors'] = [
        '#type' => 'textfield',
        '#title' => $this->t('CSS Selectors'),
        '#description' => $this->t('Enter CSS selectors to extract content (comma-separated).'),
        '#default_value' => isset($urls[$i]['selectors']) ? $urls[$i]['selectors'] : '',
      ];
      
      $form['urls_container']['url'][$i]['interval'] = [
        '#type' => 'select',
        '#title' => $this->t('Scrape Interval'),
        '#options' => [
          3600 => $this->t('Hourly'),
          86400 => $this->t('Daily'),
          604800 => $this->t('Weekly'),
          2592000 => $this->t('Monthly'),
        ],
        '#default_value' => isset($urls[$i]['interval']) ? $urls[$i]['interval'] : 86400,
      ];
    }
    
    $form['urls_container']['actions'] = [
      '#type' => 'actions',
    ];
    
    $form['urls_container']['actions']['add_url'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another URL'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'urls-fieldset-wrapper',
      ],
    ];
    
    if ($url_count > 1) {
      $form['urls_container']['actions']['remove_url'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove last URL'),
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addmoreCallback',
          'wrapper' => 'urls-fieldset-wrapper',
        ],
      ];
    }
    
    // Solr index selection
    $indexes = $this->getAvailableSolrIndexes();
    
    $form['solr_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Solr Settings'),
      '#open' => TRUE,
    ];
    
    $form['solr_settings']['index_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Solr Index'),
      '#description' => $this->t('Select the Solr index to use for indexing scraped content.'),
      '#options' => $indexes,
      '#default_value' => $config->get('index_id'),
      '#required' => TRUE,
    ];
    
    // Add a button to run the scraper manually
    $form['run_scraper'] = [
      '#type' => 'link',
      '#title' => $this->t('Run Scraper Now'),
      '#url' => Url::fromRoute('web_scraper.scrape'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];
    
    return parent::buildForm($form, $form_state);
  }
  
  /**
   * Ajax callback for the add more button.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['urls_container'];
  }
  
  /**
   * Submit handler for the "Add another URL" button.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $url_count = $form_state->get('url_count');
    $form_state->set('url_count', $url_count + 1);
    $form_state->setRebuild();
  }
  
  /**
   * Submit handler for the "Remove last URL" button.
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $url_count = $form_state->get('url_count');
    if ($url_count > 1) {
      $form_state->set('url_count', $url_count - 1);
    }
    $form_state->setRebuild();
  }

  /**
   * Get available Search API indexes that use Solr.
   */
  protected function getAvailableSolrIndexes() {
    $indexes = [];
    
    $index_storage = \Drupal::entityTypeManager()->getStorage('search_api_index');
    $solr_indexes = $index_storage->loadMultiple();
    
    foreach ($solr_indexes as $index_id => $index) {
      $server = $index->getServerInstance();
      if ($server && $server->getBackendId() == 'search_api_solr') {
        $indexes[$index_id] = $index->label();
      }
    }
    
    return $indexes;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate URLs
    $urls = $form_state->getValue(['urls_container', 'url']);
    foreach ($urls as $i => $url_config) {
      if (!empty($url_config['url'])) {
        // Basic URL validation already done by the URL form element
        // Add additional validation if needed
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('web_scraper.settings');
    
    // Depuración: Imprimir valor del índice seleccionado
    $index_id = $form_state->getValue(['index_id']);
    \Drupal::messenger()->addStatus($this->t('Trying to save index ID: @index_id', ['@index_id' => $index_id]));
    
    // Save URLs and their configurations
    $urls = [];
    $url_values = $form_state->getValue(['urls_container', 'url']);
    
    foreach ($url_values as $i => $url_config) {
      if (!empty($url_config['url'])) {
        $url_data = [
          'url' => $url_config['url'],
          'ssl_username' => $url_config['ssl_username'],
          'selectors' => $url_config['selectors'],
          'interval' => $url_config['interval'],
        ];
        
        // Only save password if it was changed
        if (!empty($url_config['ssl_password'])) {
          $url_data['ssl_password'] = $url_config['ssl_password'];
        } elseif (isset($config->get('urls')[$i]['ssl_password'])) {
          // Keep existing password
          $url_data['ssl_password'] = $config->get('urls')[$i]['ssl_password'];
        }
        
        $urls[] = $url_data;
      }
    }
    
    // Guardar configuración
    $config->set('urls', $urls)
      ->set('index_id', $index_id)
      ->save();
    
    // Depuración: Comprobar si se guardó correctamente
    $saved_index = $config->get('index_id');
    \Drupal::messenger()->addStatus($this->t('Saved index ID: @index_id', ['@index_id' => $saved_index]));
    
    parent::submitForm($form, $form_state);
  }
}