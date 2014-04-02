<?php

/*
 * pkanbanize class v2.0
 */

namespace Api;

/**
 * api call
 */
class pkanbanize {
    private $_function;
    private $_data;
    
    protected $_url;
    protected $_key;

    public function __construct ($url, $key) {
        $this->_url = $url;
        $this->_key = $key;
    }
    public function data ($data) {
        $this->_data = $data;
    }
    public function call ($function) {

        $url = $this->_url;
        $url .= "/$function";

        if ($format) {
            $url .= "/format/$format";
        }

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($this->_data));

        $headers[] = "apikey: ".$this->_key;
        $headers[] = "Content-type: application/json; charset=utf-8";

        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

        $res = curl_exec($handle);
        $err = curl_error($handle);
        $code = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        
        curl_close($handle);
        return array(
            'err' => $err,
            'res' => $res,
            'code'=> $code
        );
    }
}

namespace Lib;

/**
 * pkanbanize
 */
class pkanbanize {
    protected $API;

    protected $_key;
    protected $_mail;
    protected $_pass;
    protected $_url = 'kanbanize.com/index.php/api/kanbanize';
    
    public function __construct ($mail, $pass, $key, $domain=null) {

        if ($domain) {
            $domain .= '.';
        }

        $this->_mail = $mail;
        $this->_pass = $pass;
        $this->_key = $key;

        $this->_url = 'http://'.$domain.$this->_url;
        $this->API = new \Api\pkanbanize($this->_url, $this->_key);
    }
    public function __login () {
        $this->API->data(array('email' => $this->_mail, 'pass' => $this->_pass));
        return $this->API->call('login');
    }
    
}

?>