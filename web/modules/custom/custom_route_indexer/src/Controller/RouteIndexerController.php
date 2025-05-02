<?php

namespace Drupal\custom_route_indexer\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Controller\ControllerBase;

class RouteIndexerController extends ControllerBase {

  public function download($hash) {
    $routes = \Drupal::config('custom_route_indexer.settings')->get('routes') ?? [];

    foreach ($routes as $path) {
      if (md5($path) === $hash) {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
          $client = \Drupal::httpClient();
          $response = $client->get($path, ['stream' => TRUE]);
          $stream = $response->getBody();

          $filename = basename(parse_url($path, PHP_URL_PATH));
          $streamed = new StreamedResponse(function () use ($stream) {
            while (!$stream->eof()) {
              echo $stream->read(1024);
            }
          });

          $streamed->headers->set('Content-Type', $response->getHeaderLine('Content-Type'));
          $streamed->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
          return $streamed;
        }
        elseif (file_exists($path)) {
          return new BinaryFileResponse($path, 200, [], TRUE, null, TRUE, TRUE);
        }
        else {
          throw new NotFoundHttpException('Archivo no encontrado.');
        }
      }
    }

    throw new NotFoundHttpException('Ruta no válida.');
  }
}
