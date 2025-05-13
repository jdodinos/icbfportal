<?php

namespace Drupal\web_scraper\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Web Scraper service for extracting and indexing web content.
 */
class WebScraper {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a WebScraper object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    ClientInterface $http_client,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    MessengerInterface $messenger
  ) {
    $this->httpClient = $http_client;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
  }

  /**
   * Scrapes a specific URL.
   *
   * @param array $url_config
   *   The URL configuration.
   *
   * @return bool
   *   TRUE if successful, FALSE otherwise.
   */
  public function scrapeUrl(array $url_config) {
    try {
      // Set up request options
      $options = [];
      
      // Add auth if provided
      if (!empty($url_config['ssl_username']) && !empty($url_config['ssl_password'])) {
        $options['auth'] = [
          $url_config['ssl_username'],
          $url_config['ssl_password'],
        ];
      }
      
      // Make the request
      $response = $this->httpClient->request('GET', $url_config['url'], $options);
      
      // Get content if successful
      if ($response->getStatusCode() == 200) {
        $html = (string) $response->getBody();
        
        // Use the DOM to parse HTML
        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        
        // Process selectors
        $selectors = !empty($url_config['selectors']) ? explode(',', $url_config['selectors']) : ['body'];
        $content = [];
        
        foreach ($selectors as $selector) {
          $selector = trim($selector);
          
          // Convert CSS selector to XPath
          $xpathQuery = $this->cssToXPath($selector);
          
          // Extract content with XPath
          $nodes = $xpath->query($xpathQuery);
          if ($nodes && $nodes->length > 0) {
            foreach ($nodes as $node) {
              $content[] = $node->nodeValue;
            }
          }
        }
        
        // Process and store the scraped content
        if (!empty($content)) {
          $this->storeScrapedContent($url_config['url'], implode("\n\n", $content));
          return TRUE;
        }
      }
    }
    catch (RequestException $e) {
      $this->messenger->addError($this->t('Error scraping @url: @error', [
        '@url' => $url_config['url'],
        '@error' => $e->getMessage(),
      ]));
    }
    
    return FALSE;
  }
  
  /**
   * Stores scraped content as a node and indexes it to Solr.
   *
   * @param string $url
   *   The source URL.
   * @param string $content
   *   The scraped content.
   */
  protected function storeScrapedContent($url, $content) {
    try {
      // Create a node with the scraped content
      $node_storage = $this->entityTypeManager->getStorage('node');
      
      $node = $node_storage->create([
        'type' => 'web_scraped_content',
        'title' => substr($url, 0, 255),
        'field_source_url' => [
          'uri' => $url,
          'title' => 'Source',
        ],
        'field_scraped_content' => [
          'value' => $content,
          'format' => 'plain_text',
        ],
        'uid' => 1,  // Admin user
        'status' => 1,  // Published
      ]);
      
      $node->save();
      
      // Index to Solr
      $config = $this->configFactory->get('web_scraper.settings');
      $index_id = $config->get('index_id');
      
      if (!empty($index_id)) {
        $index = \Drupal\search_api\Entity\Index::load($index_id);
        if ($index) {
          $index->trackItemsUpdated('node', [$node->id()]);
          $index->indexItems();
        }
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Error storing scraped content: @error', [
        '@error' => $e->getMessage(),
      ]));
    }
  }
  
  /**
   * Convert a CSS selector to an XPath query.
   *
   * @param string $selector
   *   CSS selector.
   *
   * @return string
   *   XPath query.
   */
  protected function cssToXPath($selector) {
    // This is a simplified conversion and may not handle all CSS selectors
    
    // Handle element selectors
    if (preg_match('/^[a-z0-9]+$/i', $selector)) {
      return "//$selector";
    }
    
    // Handle class selectors
    if (strpos($selector, '.') === 0) {
      return "//*[contains(@class, '" . substr($selector, 1) . "')]";
    }
    
    // Handle ID selectors
    if (strpos($selector, '#') === 0) {
      return "//*[@id='" . substr($selector, 1) . "']";
    }
    
    // Handle element with class selector (e.g., div.content)
    if (preg_match('/^([a-z0-9]+)\.([a-z0-9_-]+)$/i', $selector, $matches)) {
      return "//{$matches[1]}[contains(@class, '{$matches[2]}')]";
    }
    
    // Default to searching the entire document
    return "//body//*";
  }
  
  /**
   * Scrapes all configured URLs.
   */
  public function scrapeAll() {
    $config = $this->configFactory->get('web_scraper.settings');
    $urls = $config->get('urls');
    
    if (empty($urls)) {
      return;
    }
    
    $success_count = 0;
    foreach ($urls as $url_config) {
      if ($this->scrapeUrl($url_config)) {
        $success_count++;
      }
    }
    
    return $success_count;
  }
}