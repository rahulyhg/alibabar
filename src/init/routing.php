<?php
$app->get('/', function() use ($app){
    return 123;
    //return $app['twig']->render("app/view/index.twig", []);
})->bind('index');
echo $app->run();