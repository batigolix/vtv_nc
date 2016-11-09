<?php

namespace Drupal\vtvmi\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Class ConvertNids.
 *
 * @package Drupal\vtvmi\Controller
 */
class ConvertNids extends ControllerBase {

  /**
   * Convert_nids.
   *
   * Finds internal urls and looks up the target NID in the migration map table.
   *
   * @return string
   *   Return Hello string.
   */
  public function convert_nids() {

    // Fetches all nodes.
    $query = \Drupal::entityQuery('node');
    $nids = $query->execute();
    foreach ($nids as $nid) {
      $node = Node::load($nid);
      if ($node->body) {
        $body = $node->body->value;

        // Replaces alternative domain names in urls.
        $body = str_replace("http://www.volkstheater-venlo.nl/nl/node", "http://www.volkstheater-venlo.nl/node", $body);

        // Finds occurences of internal urls. The double quotes are included in the search to prevent mismatches.
        preg_match_all('/\"http:\/\/www\.volkstheater-venlo\.nl\/node\/\d+\"/', $body, $matches);
        if (count($matches[0]) > 1) {
          $save = TRUE;
          foreach ($matches[0] as $match) {

            // Fetches the NID from the match.
            $explode = explode('/node/', $match);
            $source_nid = str_replace('"', '', $explode[1]);
            $target_nid = _vtvmi_get_local_nid($source_nid);
            if ($target_nid) {

              // Replaces match containing source NID with target NID.
              $body = str_replace($match, '"/node/' . $target_nid . '"', $body);
            }
          }
        }
        else {

          // Sets boolean to decide whether node needs to be saved.
          $save = FALSE;
        }
        if ($save) {
          $node->body->value = $body;
          $node->save();
        }
      }
    }
    return [
      '#type' => 'markup',
      '#markup' => 'converting node ids',
    ];
  }
}
