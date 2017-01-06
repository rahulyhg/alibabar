<?php
//ui
$app->get('/', function() use ($app){
    return $app['twig']->render(
        "app/view/index.twig",
        ['bartender' => $_SERVER['HOSTNAME']]
    );
})->bind('index');

//api
$app->get('/api/init', function() use($app){
    $data = [];
    $dbh = $app['pdo'];
    $redis = $app['redis'];

    //menu
    $stmt = $dbh->prepare("SELECT * FROM booze");
    $stmt->execute();
    $menu = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    //rand
    /*
    $ranking = $redis->zRevRange('top-drinkers', 0, -1, true);
    $ranking_drinks = $redis->zRevRange('top-drinks', 0, -1, true);
    */

    $data['menu'] = $menu;
    $data['drunk'] = $_SESSION['drinks'];
    $data['my-messages'] = $_SESSION['messages'];
    //$data['last'] = $last_orders;
    //$data['top-drinkers'] = $ranking;
    //$data['top-drinks'] = $ranking_drinks;
    //$n = count($last_orders);

    return $app->json($data);
})->bind('api-init');

echo $app->run();