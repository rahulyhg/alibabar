<?php
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\TwigServiceProvider(), ['twig.path' => $config->twig_path]);

$app['twig'] = $app->share($app->extend('twig', function (Twig_Environment $twig) use ($config, $app) {
    $twig->addGlobal('_config',$config);
    return $twig;
}));
$app['Config'] = $app->share(function() use ($config){
    $c = new \app\misc\Config();
    return $config;
});
$app['SessionHandler'] = $app->share(function() use ($app){
    return new \app\misc\SessionHandler($app['Repo']->session());
});

//services