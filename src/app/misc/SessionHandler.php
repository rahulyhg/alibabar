<?php
namespace app\misc;

class SessionHandler implements \SessionHandlerInterface {
    public $ttl = 18000; // 300 minutes default
    protected $db;
    protected $prefix;

    function __construct(\Redis $db, $prefix = 'PHP_SESSION:') {
        $this->db = $db;
        $this->prefix = $prefix;
    }

    function open($savePath, $sessionName) {
        return true;
    }

    function close() {
        $this->db->close();
    }

    function read($id) {
        $id = $this->prefix . $id;
        $sessData = $this->db->get($id);
        $this->db->expire($id, $this->ttl);
        return $sessData;
    }

    function write($id, $data) {
        $id = $this->prefix . $id;
        $this->db->set($id, $data);
        $this->db->expire($id, $this->ttl);
    }

    function destroy($id) {
        $this->db->del($this->prefix . $id);
    }

    function gc($maxLifetime) {
        // no action necessary because using EXPIRE
    }
}