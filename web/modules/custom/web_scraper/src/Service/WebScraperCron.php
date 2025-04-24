<?php

namespace Drupal\web_scraper\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;

/**
 * Service for handling cron-based web scraping.
 */
class WebScraperCron {

  /**
   * The Web Scraper service.
   *
   * @var \Drupal\web_scraper\Service\WebScraper
   */
  protected $webScraper;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a WebScraperCron object.
   *
   * @param \Drupal\web_scraper\Service\WebScraper $web_scraper
   *   The Web Scraper service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(WebScraper $web_scraper, ConfigFactoryInterface $config_factory, StateInterface $state) {
    $this->webScraper = $web_scraper;
    $this->configFactory = $config_factory;
    $this->state = $state;
  }

  /**
   * Implements hook_cron().
   */
  public function cron() {
    $config = $this->configFactory->get('web_scraper.settings');
    $urls = $config->get('urls');
    
    if (empty($urls)) {
      return;
    }
    
    $current_time = \Drupal::time()->getRequestTime();
    $last_run = $this->state->get('web_scraper.last_run', []);
    
    foreach ($urls as $key => $url_config) {
      // Skip URLs without interval
      if (empty($url_config['interval'])) {
        continue;
      }
      
      // Check if this URL is due for scraping
      $url_last_run = isset($last_run[$key]) ? $last_run[$key] : 0;
      if ($current_time - $url_last_run >= $url_config['interval']) {
        // Scrape this URL
        $this->webScraper->scrapeUrl($url_config);
        
        // Update last run time
        $last_run[$key] = $current_time;
      }
    }
    
    // Save last run times
    $this->state->set('web_scraper.last_run', $last_run);
  }
}