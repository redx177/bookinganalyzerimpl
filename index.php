<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$config = new Config();

$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, array(
    //'cache' => __DIR__ . '/compilation_cache',
));

$template = $twig->load('explore.twig', array('pageName' => "Attributanalysis"));
echo $template->render();