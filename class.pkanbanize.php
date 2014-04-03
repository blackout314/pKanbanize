<?php

/**
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
            'code'=> $code,
        );
    }
}

namespace Lib;

/**
 * pkanbanize
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

        $this->_url = 'http://' . ($domain ? $domain.'.' : '') . $this->_url;
        $this->API = new \Api\pkanbanize($this->_url, $this->_key);
        
        if ($mail && $pass) {
            $this->_mail = $mail;
            $this->_pass = $pass;
            $this->_key = $this->__login()['res']->apikey;
            $this->API->setKey( $this->_key );
        }
    }
    // private __login function
    private function __login () {
        $this->API->data(array('email' => $this->_mail, 'pass' => $this->_pass));
        return $this->API->call('login');
    }
    
	/**
	 * @name getAllTasks
	 *
	 * @param {int} boardid
	 * @param {Boolean} subtasks Set to true if you want to get subtask details for each task
	 *
	 * @return {Array} all tasks
	 */
    public function getAllTasks ($boardid, $subtasks = false) {
        $this->API->data(array(
		    'boardid' => $boardid,
		    'subtasks' => $subtasks ? 'yes' : false,
        ));
        return $this->API->call('get_all_tasks');
    }

	/**
	 * @name getBoardActivities
	 *
	 * @param {int} boardid
	 * @param {string} fromdate
	 * @param {string} todate
	 * @param {int} [page] default 1
	 * @param {int} [resultsperpage] default 30
	 * @param {string} [author] default ALL
	 * @param {string} [eventtype] Transitions, Updates, Comments, Blocks. Default is ALL
	 * @param {string} [textformat] "plain" (default) and "html". If the plain text format is used, the HTML tags are stripped from the history details
	 *
	 * @return {Array} all activities 
	 * - allactivities : The number of all activities for the corresponding time window specified by the fromdate and todate parameters.
	 * - page : current page
	 * - activities[] : author, event, text, date, taskid
	 */
	public function getBoardActivities ($boardid, $fromdate, $todate, $page=1, $resultsperpage=30, $author=null, $eventtype=null, $textformat='plain') {
		$this->API->data( array(
			'boardid' => $boardid, 
			'fromdate' => $fromdate, 
			'todate' => $todate,
			'page' => $page,
			'resultsperpage' => $resultsperpage,
			'author' => $author,
			'eventtype' => $eventtype,
			'textformat' => $textformat
		) );
		return $this->API->call('get_board_activities');
	}

	/**
	 * @name getTaskDetails
	 *
	 * @param int  $boardid The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int  $taskid The ID of the task to be deleted.
	 * @param bool $history if you want to get history for the task.
	 *
	 * @return array
	 */
	public function getTaskDetails ($boardid, $taskid, $history = false) {
		$this->API->data(array(
		    'boardid' => $boardid, 
		    'taskid' => $taskid,
		    'history' => $history ? 'yes' : false
        ));
		return $this->API->call('get_task_details');
	}

	/**
	 * @name getBoardSettings
	 * @desc get_board_settings method. Limit 30/hour
	 *
	 * @param int $boardid The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 *
	 * @return array
	 *
	 * - usernames Array containing the usernames of the board members.
	 * - templates Array containing the templates available to this board.
	 * - types     Array containing the types available to this board.
	 */
	public function getBoardSettings ($boardid) {
		$this->API->data(array('boardid' => $boardid));
		return $this->API->call('get_board_settings');
	}

	/**
	 * @name getBoardStructure
	 * @desc get_board_structure method. Limit 30/hour
	 *
	 * @param int $boardid The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 *
	 * @return array
	 *
	 * - columns    Array containing the board columns (only the columns on last level are returned)
	 * - columns[][position]       The position of the column
	 * - columns[][lcname]         The name of the column.
	 * - columns[][description]    The description of the column or swimlane.
	 * - lanes      Array containing the board swimnales.
	 * - lanes[][lcname]         The name of the swimlane.
	 * - lanes[][color]          The color of the swimlane.
	 * - lanes[][description]    The description of the column or swimlane.
	 */
	public function getBoardStructure ($boardid) {
        $this->API->data(array('boardid' => $boardid));
		return $this->API->call('get_board_structure');
	}

	/**
	 * @name editTask
	 *
	 * @param int   $boardid The ID of the board where the task to be moved is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int   $taskid The ID of the task to be deleted.
	 *
	 * @param array $changeData
	 *
	 * - title         Title of the task
	 * - description Description of the task
	 * - priority    One of the following: Low, Average, High
	 * - assignee    Username of the assignee (must be a valid username)
	 * - color       Any color code (e.g. 99b399) DO NOT PUT the # sign in front of the code!!!
	 * - size        Size of the task
	 * - tags        Space separated list of tags
	 * - deadline    Dedline in the format: yyyy-mm-dd (e.g. 2011-12-13)
	 * - extlink     A link in the following format: https:\\github.com\philsturgeon. If the parameter is embedded in the request BODY
	 * - type        The name of the type you want to set.
	 *
	 * @return 1|string|false
	 */
	public function editTask($boardid, $taskid, $changeData = array()) {
		$d = array('boardid' => $boardid, 'taskid' => $taskid);
		foreach ($changeData as $k => $v) {
			$d[$k] = $v;
		}

		$this->API->data($d);

		$resp = $this->API->call('edit_task');
		if ($resp) {
			return $resp['code'] ? : false;
		}
		return false;
	}

}

// -- eof
?>