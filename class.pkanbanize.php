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
    private $_format;
    
    protected $_url;
    protected $_key;

    public function __construct ($url, $key, $format='json') {
        $this->_url = $url;
        $this->_key = $key;
        $this->_format = $format;
    }
    public function setKey ($key) {
        $this->_key = $key;
    }
    public function data ($data) {
        $this->_data = $data;
    }
    public function call ($function) {
        $headers = array(); // #TODO
        
        $url = $this->_url;
        $url .= "/$function";

        if ($this->_format) {
            $url .= "/format/".$this->_format;
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
            'res' => $this->_format==='json' ? json_decode($res) : $res,
            'code'=> $code
        );
    }
}

namespace Lib;

/**
 * pkanbanize
 *
 * 
 * $k = new \Lib\pkanbanize( YOURKEY ); || $k = new \Lib\pkanbanize( null,null, USER,PASS );
 * $task = $k->getAllTasks(6);
 * echo print_r($task['res']);
 * 
 */
class pkanbanize {
    protected $API;

    protected $_key;
    protected $_mail;
    protected $_pass;
    protected $_url = 'kanbanize.com/index.php/api/kanbanize';
    
    public function __construct ($key=null, $domain=null, $mail=null, $pass=null) {
        
        if ($key) {
            $this->_key = $key;
        }
        
        if ($domain) {
            $domain .= '.';
        }

        $this->_url = 'http://'.$domain.$this->_url;
        $this->API = new \Api\pkanbanize($this->_url, $this->_key);
        
        if ($mail && $pass) {
            $this->_mail = $mail;
            $this->_pass = $pass;
            $res = $this->__login();
            $this->_key = $res['res']->apikey;
            $this->API->setKey( $this->_key );
        }
    }
    private function __login () {
        $this->API->data(array('email' => $this->_mail, 'pass' => $this->_pass));
        return $this->API->call('login');
    }
    
	/**
	 * @name getAllTasks
	 *
	 * @param {int} boardid
	 *
	 * @return {Array} all tasks
	 */
	public function getAllTasks ($boardid) {
		$this->API->data(array('boardid' => $boardid));
		return $this->API->call('get_all_tasks');
	}
    
}

?>