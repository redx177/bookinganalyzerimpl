<?php
require_once __DIR__ . '/vendor/autoload.php';

echo exec('whoami');

$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, array(
    'cache' => __DIR__ . '/compilation_cache',
));

$template = $twig->load('test.html');
echo $template->render();