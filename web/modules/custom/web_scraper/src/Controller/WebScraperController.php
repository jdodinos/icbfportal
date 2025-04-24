<?php

namespace Drupal\web_scraper\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\search_api\Entity\Index;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Controller for Web Scraper.
 */
class WebScraperController extends ControllerBase {

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
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a WebScraperController object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(ClientInterface $http_client, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger) {
    $this->httpClient = $http_client;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('entity_type.manager'),
      $container->get('messenger')
    );
  }

  /**
   * Scrapes websites and indexes content to Solr.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response.
   */
  public function scrape() {
    $config = $this->config('web_scraper.settings');
    $urls = $config->get('urls');
    $index_id = $config->get('index_id');
    
    if (empty($urls)) {
      $this->messenger->addError($this->t('No URLs configured for scraping.'));
      return new RedirectResponse(Url::fromRoute('web_scraper.config_form')->toString());
    }
    
    if (empty($index_id)) {
      $this->messenger->addError($this->t('No Solr index selected.'));
      return new RedirectResponse(Url::fromRoute('web_scraper.config_form')->toString());
    }
    
    // Load the Search API index
    $index = Index::load($index_id);
    if (!$index) {
      $this->messenger->addError($this->t('Selected Solr index does not exist.'));
      return new RedirectResponse(Url::fromRoute('web_scraper.config_form')->toString());
    }
    
    // For storing extracted content
    $scraped_items = [];
    
    // Process each URL
    foreach ($urls as $url_config) {
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
          $selectors = explode(',', $url_config['selectors']);
          $content = [];
          
          foreach ($selectors as $selector) {
            $selector = trim($selector);
            
            // Convert CSS selector to XPath (simplified)
            $xpathQuery = $this->cssToXPath($selector);
            
            // Extract content with XPath
            $nodes = $xpath->query($xpathQuery);
            if ($nodes && $nodes->length > 0) {
              foreach ($nodes as $node) {
                $content[] = $node->nodeValue;
              }
            }
          }
          
          // Add scraped data to items
          if (!empty($content)) {
            $scraped_items[] = [
              'source_url' => $url_config['url'],
              'content' => implode("\n\n", $content),
              'timestamp' => \Drupal::time()->getRequestTime(),
            ];
          }
        }
      }
      catch (RequestException $e) {
        $this->messenger->addError($this->t('Error scraping @url: @error', [
          '@url' => $url_config['url'],
          '@error' => $e->getMessage(),
        ]));
      }
    }
    
    // Create nodes with the scraped content and index them
    if (!empty($scraped_items)) {
      $node_storage = $this->entityTypeManager->getStorage('node');
      $nodes_created = 0;
      
      foreach ($scraped_items as $item) {
        // Create a node with the scraped content
        $node = $node_storage->create([
          'type' => 'web_scraped_content',  // You need to create this content type
          'title' => substr($item['source_url'], 0, 255),
          'field_source_url' => $item['source_url'],
          'field_scraped_content' => $item['content'],
          'created' => $item['timestamp'],
          'changed' => $item['timestamp'],
          'uid' => 1,  // Admin user
          'status' => 1,  // Published
        ]);
        
        $node->save();
        $nodes_created++;
      }
      
      // Index content to Solr
      $index->indexItems();
      
      $this->messenger->addStatus($this->t('@count items scraped and indexed to Solr.', ['@count' => $nodes_created]));
    }
    else {
      $this->messenger->addWarning($this->t('No content was extracted from the configured URLs.'));
    }
    
    return new RedirectResponse(Url::fromRoute('web_scraper.config_form')->toString());
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
    
    // Default to searching the entire document
    return "//body//*";
  }
}