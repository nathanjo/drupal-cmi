<?php

namespace Drupal\demo\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DemoController {

  public function content() {
    return array(
      '#markup' => t("Hello World"),
    );
  }
  
}