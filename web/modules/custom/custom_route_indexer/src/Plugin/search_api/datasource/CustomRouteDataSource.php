<?php

namespace Drupal\custom_route_indexer\Plugin\search_api\datasource;

use Drupal\search_api\Datasource\DatasourcePluginBase;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\search_api\Item\Field;
use Drupal\search_api\Item\ItemInterface;
use Drupal\Core\Url;

/**
 * Data source para indexar rutas personalizadas.
 *
 * @SearchApiDataSource(
 *   id = "custom_route_indexer",
 *   label = "Custom Route Indexer",
 *   description = "Indexa archivos de rutas configuradas.",
 * )
 */
class CustomRouteDataSource extends DataSourcePluginBase {

  public function getItemId($item): string {
    return md5($item['path']);
  }

  public function getItem($item_id) {
    $routes = \Drupal::config('custom_route_indexer.settings')->get('routes') ?? [];

    foreach ($routes as $path) {
      if (md5($path) === $item_id) {
        return [
          'path' => $path,
          'label' => basename($path),
          'url' => $this->buildDownloadUrl($path),
        ];
      }
    }

    return NULL;
  }

  public function getItemLabel($item) {
    return $item['label'] ?? '';
  }

  public function getItemUrl($item): ?Url {
    return NULL;
  }

  public function getItems(array $ids) {
    $items = [];
    foreach ($ids as $id) {
      $item = $this->getItem($id);
      if ($item) {
        $items[$id] = $item;
      }
    }
    return $items;
  }

  public function getAllItems() {
    $routes = \Drupal::config('custom_route_indexer.settings')->get('routes') ?? [];
    $items = [];
  
    foreach ($routes as $path) {
      if (filter_var($path, FILTER_VALIDATE_URL)) {
        $items[md5($path)] = [
          'path' => $path,
          'label' => basename(parse_url($path, PHP_URL_PATH)),
          'url' => $this->buildDownloadUrl($path),
        ];
        continue;
      }
  
      $real_path = realpath($path);
      if (!$real_path || !file_exists($real_path)) {
        continue;
      }
  
      if (is_file($real_path)) {
        $items[md5($real_path)] = [
          'path' => $real_path,
          'label' => basename($real_path),
          'url' => $this->buildDownloadUrl($real_path),
        ];
      }
      elseif (is_dir($real_path)) {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($real_path));
        foreach ($rii as $file) {
          if ($file->isFile()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['pdf', 'doc', 'docx', 'txt', 'odt'])) {
              $full_path = $file->getPathname();
              $items[md5($full_path)] = [
                'path' => $full_path,
                'label' => basename($full_path),
                'url' => $this->buildDownloadUrl($full_path),
              ];
            }
          }
        }
      }
    }
  
    return $items;
  }
  
  

  public function addFields(ItemInterface $item) {
    $field = new Field($this->getPluginId(), 'file_content', $this->t('File content'));
    $item->setField('file_content', $field);
  }

  protected function buildDownloadUrl(string $path): string {
    return Url::fromRoute('custom_route_indexer.download', ['hash' => md5($path)], ['absolute' => TRUE])->toString();
  }

  public function getPropertyDefinitions(ItemInterface $item = NULL) {
    $properties = [];
  
    $properties['label'] = DataDefinition::create('string')
      ->setLabel(t('Label'));
  
    $properties['url'] = DataDefinition::create('string')
      ->setLabel(t('Document URL'));
  
    return $properties;
  }
  
}
