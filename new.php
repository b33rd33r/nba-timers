<?php

  require_once __DIR__ . '/vendor/autoload.php';
  require_once __DIR__ . '/vars.php';

  $loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');

  $twig = new Twig_Environment($loader, array(
      'cache' => __DIR__ . '/cache',
      'debug' => true,
  ));

  $twig->addExtension(new Twig_Extension_Debug());

  echo $twig->render('index.html', array('vars' => $teams));

