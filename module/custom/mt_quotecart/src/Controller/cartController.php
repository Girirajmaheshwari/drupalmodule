<?php
/**
 * @file
 * Contains \Drupal\mt_quotecart\Controller\cartController
 */

namespace Drupal\mt_quotecart\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\redirect\Entity\Redirect;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * cartController
 */
class cartController extends ControllerBase {


  public function _CartContent() {
    global $base_url; //unset($_SESSION['cart']);
      //\Drupal::request()->get('arg1');
    $pid = \Drupal::request()->get('arg1');
    $_SESSION['quotecart'][$pid] =  $pid;

   // $url = $base_url."/quotecart";
    $url = $base_url."/products";
    return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
  }

  public function _removeCartitem() {
    global $base_url;
    $pid = \Drupal::request()->get('arg1');
    unset($_SESSION['quotecart'][$pid]);

    $url = $base_url."/quotecart";
    return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
  }


}
