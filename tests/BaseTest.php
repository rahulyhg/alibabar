<?php
namespace tests; 

use app\misc\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * @codeCoverageIgnore
 */
class BaseTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \Silex\Application
     */
    protected $_app;

    /**
     * @var Config
     */
    protected $_cfg;
    /**
     * @var UrlGenerator
     */
    protected $_gen;
    /**
     * @var SilexClient
     */
    protected $_client;

    function __construct(){
        $this->_app = Bootstrap::getInstance();
        $this->_gen = $this->_app['url_generator'];
        $this->_cfg = $this->_app['config'];
        parent::__construct();
    }
    
    protected function setUp(){
        $this->_client = new SilexClient($this->_app);
        parent::setUp(); 
    }

    protected function _file($put, $mime, $name){
        $file = tempnam(sys_get_temp_dir(), uniqid('test'));
        file_put_contents($file, $put);
        $file = new UploadedFile($file, $name, $mime, null, null, true);
        return $file;
    }
} 