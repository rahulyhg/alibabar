<?php
namespace tests;

/**
 * Singleton Initializer for test-cases.
 */
use app\misc\TokenHandler;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

Bootstrap::getInstance();
class Bootstrap {
    private static $_inst;
    
    private function __construct(){}

    /**
     * @return \Silex\Application
     */
    public static function getInstance(){
        if(!self::$_inst){
            // Enable all errors
            error_reporting(- 1);
            ini_set('display_startup_errors', 1);
            ini_set('display_errors', 1);

            $app = null;
            $db = null;
            $gen = new \PHPUnit_Framework_MockObject_Generator();
            require_once __DIR__.'/../vendor/autoload.php';
            require_once file_exists(__DIR__.'/../config/test.php') ?
                __DIR__.'/../config/test.php' : __DIR__ . '/../config/local.php.dist';
            require_once __DIR__.'/../src/init/app.php';
            require_once __DIR__.'/../src/init/di.php';
            require_once __DIR__.'/../src/init/routing.php';
            $_SESSION = [];
            $_SESSION['drinks'] = [];
            $_SESSION['messages'] = [];
            self::$_inst = $app;
        }
        return self::$_inst;
    }
}
class SilexClient extends \PHPUnit_Framework_Assert{
    protected $_app;
    protected $_terminate;

    function __construct(Application $_app, $_terminate = true){
        $this->_app = $_app;
        $this->_terminate = $_terminate;
    }
    
    public function get($respStatus, $url, $respContent = null, $params = [], $cookies = [], $headers = []){
        $_GET = is_null($params) ? [] : $params;
        return $this->_request($url, 'GET', $respStatus, $respContent, $params, $cookies, [], $headers);
    }
    public function post(
        $respStatus,
        $url,
        $respContent = null,
        $params = [],
        $cookies = [],
        $files = [],
        $headers = []
    ){
        $_POST = is_null($params) ? [] : $params;
        $_FILES = is_null($files) ? [] : $files;
        return $this->_request($url, 'POST', $respStatus, $respContent, $params, $cookies, $files, $headers);
    }
    public function delete($respStatus, $url, $respContent = null, $params = [], $cookies = [], $files = [], $headers = []){
        return $this->_request($url, 'DELETE', $respStatus, $respContent, $params, $cookies, $files, $headers);
    }

    protected function _request(
        $url,
        $method,
        $respStatus = null,
        $respContent = null,
        $params = [],
        $cookies = [],
        $files = [],
        $headers = []
    ){
        $r = Request::create($url, $method, $params, $cookies, $files, $_SERVER);
        $r->headers->add($headers);
        $resp = $this->_app->handle($r);
        if ($this->_terminate){
            $this->_app->terminate($r, $resp);
        }
        if (!is_null($respStatus)){
            $this->assertEquals($respStatus, $resp->getStatusCode(), "$method: $url");
        }
        if (!is_null($respContent)){
            $this->assertEquals($respContent, $resp->getContent());
        }
        return $resp;
    }
}
trait ProxyTrait{
    public function __call($name, array $arguments){
        if(method_exists($this, $name) !== true){
            throw new \Exception("Infinite recursion.");
        }
        return call_user_func_array(
            array($this, $name),$arguments
        );
    }
}
class MockPDO extends \PDO{
    function __construct(){}
    function execute(){}
    function fetchAll(){}
}