<?php
$app = new Silex\Application();
$app['debug'] = false;

$app->before(function () use ($app){
    static $started = false;
    static $userLoaded = false;

    if (php_sapi_name() == 'cli'){
        return;
    }

    if (!$started) {
        session_set_save_handler($app['SessionHandler']);
        session_start();
        $started = true;

        if (!isset($_SESSION['drinks'])){
            $_SESSION['drinks'] = [];
        }
        if (!isset($_SESSION['messages'])){
            $_SESSION['messages'] = [];
        }
    }

    //ensures app doesn't short circuits into loops because of PDO not being available
    if ($userLoaded) {
        return;
    }

    $userLoaded = true;

    //init user
    //$user = !empty($_SESSION[$app['config']['session']['user']]) ? $_SESSION[$app['config']['session']['user']] : null;

    //load user
    //$app['user'] = $user;
});
$app->error(function (\Exception $e) use ($app, $config) {
    $log = function (\Exception $e) {
        $msg = [];
        $msg[] = $e->getMessage();
        $msg[] = $e->getTraceAsString();
        while (true) {
            /** @var Exception $prev */
            $prev = $e->getPrevious();
            if (empty($prev)) {
                break;
            }
            $msg[] = $prev->getMessage();
            $msg[] = $prev->getTraceAsString();
            $e = $prev;
        }
        $msg = implode('', $msg);
        @error_log($msg);
    };

    //cast to app exceptions
    if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
        $e = new \app\misc\ServiceException(
            405, 0, '', \app\misc\Constants::E_NOT_ALLOWED, 'Method not allowed.'
        );
    }
    if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
        $e = new \app\misc\ServiceException(
            404, 0, '', \app\misc\Constants::E_NOT_FOUND, 'Not found.'
        );
    }

    $httpStatus = null;
    $code = 500;
    if ($e instanceof \app\misc\ServiceException) {
        //log any server message
        $serverMsg = $e->getMessage();
        if (!empty($serverMsg)) {
            $log($e);
        }
        $code = $httpStatus = $e->getHttpStatus();
    } else {
        //log any other exception
        $log($e);
    }

    $data = $e instanceof \app\misc\ServiceException ?
        $e->toArray() :
        [
            'http_status_code' => 500,
            'client_code' => '',
            'message' => 'A server error has occurred.',
            'details' => null
        ]
    ;
    $status = $httpStatus ? : $code;
    return $app->json($data, $status);
});