<?php
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\TwigServiceProvider(), ['twig.path' => __DIR__ . '/../']);

$app['twig'] = $app->share($app->extend('twig', function (Twig_Environment $twig) use ($config, $app) {
    $twig->addGlobal('_config',$config);
    return $twig;
}));
$app['config'] = $app->share(function() use ($config){
    return $config;
});
$app['SessionHandler'] = $app->share(function() use ($app, $config){
 return new \app\misc\SessionHandler($app['redis']);
});
$app['redis'] = $app->share(function() use ($config){
    $redis = new \Redis();
    $redis->connect($config->redis_session[0], $config->redis_session[1]);
    return $redis;
});
$app['pdo'] = $app->share(function() use ($app, $config){
    $pdo = new \PDO(
        $config->mysql_dsn,
        $config->mysql_user,
        $config->mysql_password,
        [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
        ]
    );
    return $pdo;
});

//services