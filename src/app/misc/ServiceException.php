<?php
namespace app\misc;

use Exception;

class ServiceException extends \Exception{
    protected $_httpStatus;

    protected $_clientCode;
    protected $_clientMsg;
    protected $_details;

    function __construct(
        $httpStatus,
        $serverCode = 0,
        $serverMessage = '',

        $clientCode = null,
        $clientMessage = '',
        $details = null,
        Exception $prev = null
    ){
        $this->_httpStatus = $httpStatus;
        $this->_clientCode = $clientCode;
        $this->_clientMsg = $clientMessage;
        $this->_details = $details;
        parent::__construct($serverMessage, $serverCode, $prev);
    }

    function getHttpStatus(){
        return $this->_httpStatus;
    }

    function toArray(){
        return [
            'http_status_code' => $this->_httpStatus,
            'client_code' => $this->_clientCode,
            'message' => $this->_clientMsg,
            'details' => $this->_details
        ];
    }
} 