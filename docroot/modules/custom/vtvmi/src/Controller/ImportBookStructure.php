<?php

namespace Drupal\vtvmi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;

/**
 * Class ImportBookStructure.
 *
 * @package Drupal\vtvmi\Controller
 */
class ImportBookStructure extends ControllerBase {
  /**
   * Import_book_structure.
   *
   * @return string
   *   Return Hello string.
   */
  public function import_book_structure() {
    $uri = 'http://boermans.val/boe-book-structure.json';
    try {
      $response = \Drupal::httpClient()
        ->get($uri, array('headers' => array('Accept' => 'text/plain')));
      $data = (string) $response->getBody();
      $data = json_decode($data);
      if (empty($data)) {
        return FALSE;
      }
      else {
        foreach ($data as $datum) {
          $nid = _vtvmi_get_local_nid($datum->nid);
          $pid = $datum->pid > 0 ? _vtvmi_get_local_nid($datum->pid) : 0;
          $bid = _vtvmi_get_local_nid($datum->bid);
          if ($nid && $bid && $pid >= 0) {
            $query = \Drupal::database()->upsert('book');
            $query->fields([
              'nid',
              'bid',
              'pid',
              'has_children',
              'weight',
              'depth',
              'p1',
              'p2',
              'p3',
              'p4',
              'p5',
              'p6',
              'p7',
              'p8',
              'p9',
            ]);
            $query->values([
              $nid,
              $bid,
              $pid,
              $datum->has_children,
              $datum->weight,
              $datum->depth,
              $datum->p1 > 0 ? _vtvmi_get_local_nid($datum->p1) : 0,
              $datum->p2 > 0 ? _vtvmi_get_local_nid($datum->p2) : 0,
              $datum->p3 > 0 ? _vtvmi_get_local_nid($datum->p3) : 0,
              $datum->p4 > 0 ? _vtvmi_get_local_nid($datum->p4) : 0,
              $datum->p5 > 0 ? _vtvmi_get_local_nid($datum->p5) : 0,
              $datum->p6 > 0 ? _vtvmi_get_local_nid($datum->p6) : 0,
              $datum->p7 > 0 ? _vtvmi_get_local_nid($datum->p7) : 0,
              $datum->p8 > 0 ? _vtvmi_get_local_nid($datum->p8) : 0,
              $datum->p9 > 0 ? _vtvmi_get_local_nid($datum->p9) : 0,
            ]);
            $query->key('nid');
            $query->execute();
          }
        }
      }
    }
    catch (RequestException $e) {
      echo $e;
      return FALSE;
    }
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: import_book_structure')
    ];
  }

}
