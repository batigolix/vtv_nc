<?php

namespace Drupal\vtv_tools\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AddBooks.
 *
 * @package Drupal\vtv_tools\Controller
 */
class AddBooks extends ControllerBase {
  /**
   * Add_books.
   *
   * @return string
   *   Return Hello string.
   */
  public function add_books() {





    $book_manager = \Drupal::service('book.manager');

    // Fetches the slideshow nodes.
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'book')
           ->sort('nid')
      ->range(0, 9);
    $nids = $query->execute();
    $items = array();
    foreach ($nids as $nid) {
      $node = entity_load('node', $nid);

      $parent = $book_manager->loadBookLink($query->get('parent'), TRUE);


      kint($node->toArray());
//      $node_view = entity_view($node, 'hero_teaser');
//      $items[] = array(
//        '#markup' => drupal_render($node_view),
//        '#wrapper_attributes' => array('class' => array('slide')),
//      );
    }


    kint($nids);


    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: add_books')
    ];
  }

}
