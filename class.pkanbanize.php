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
 */
class pkanbanize {
	protected $API;

	protected $_key;
	protected $_mail;
	protected $_pass;
	protected $_url = 'kanbanize.com/index.php/api/kanbanize';

	/**
	 * @param {String} [key] api key
	 * @param {String} [domain] domain
	 * @param {String} [mail] mail
	 * @param {String} [pass] password
	 * @param {String} [format] json/xml
	 */
	public function __construct ($key=null, $domain=null, $mail=null, $pass=null, $format=null) {
		if ($key) {
			$this->_key = $key;
		}

		$this->_url = 'http://' . ($domain ? $domain.'.' : '') . $this->_url;
		$this->API = new \Api\pkanbanize($this->_url, $this->_key, $format);

		if ($mail && $pass) {
			$this->_mail = $mail;
			$this->_pass = $pass;
			$login = $this->__login();
			$this->_key = $login['res']->apikey;
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
     * @name createNewTask
     * @desc create_new_task method. Limit 30/hour
     *
     * @param int $boardid The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
     * @param array $data
     *
     * - title Title of the task
     * - description Description of the task
     * - priority One of the following: Low, Average, High
     * - assignee Username of the assignee (must be a valid username)
     * - color Any color code (e.g. 99b399) DO NOT PUT the # sign in front of the code!!!
     * - size Size of the task
     * - tags Space separated list of tags
     * - deadline Dedline in the format: yyyy-mm-dd (e.g. 2011-12-13)
     * - extlink A link in the following format: https:\\github.com\philsturgeon. If the parameter is embedded in the request BODY
     * - type The name of the type you want to set.
     * - template The name of the template you want to set. If you specify any property as part of the request, the one specified in the template will be overwritten.
     *
     * @return int|false
     */
    public function createNewTask($boardid, $data = array()) {
    	$d = array();
    	foreach ($data as $k => $v) {
    		$d[$k] = $v;
    	}
    	$d['boardid'] = $boardid;

    	$this->API->data($d);

    	$res = $this->API->call('create_new_task');
    	$res = $res['res'];
    	if ($res) {
    		return @$res['taskid'] ? : @$res['id'] ? : false;
    	}

    	return false;
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

    	$res = $this->API->call('edit_task');
    	$res = $res['res'];
    	if ($res) {
    		return $res['code'] ? true : false;
    	}
    	return false;
    }
    /**
     * @name deleteTask
     *
     * @param int $boardid The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
     * @param int $taskid The ID of the task to be deleted.
     *
     * @return 1|string|false
     */
    public function deleteTask($boardid, $taskid) {
    	$this->API->data( array(
    		'boardid' => $boardid, 
    		'taskid' => $taskid,
    		) );

    	$res = $this->API->call('delete_task');
    	$res = $res['res'];
    	if ($res) {
    		return @$res['status'] ? : false;
    	}
    	return false;
    }

    public function getLinks ($board,$task) {
    	$this->API->data(array(
    		'boardid' => $board,
    		'taskid' => $task
    		));
    	$res = $this->API->call('get_links');
    	if ($res) {
    		return $res;
    	}
    	 return false;
    }

    /**
     * @name moveTask
     * @desc move_task method. Limit 60/hour
     *
     * @param int $boardid The ID of the board where the task to be moved is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
     * @param int $taskid The ID of the task to be deleted.
     * @param string $column The name of the column to move the task into. If the name of the column is unique, you can specify it alone, but if there are more than one columns with that name, you must specify it as columnname1 . columnname2 . columnname3
     *
     * @param array $options
     *
     * - lane The name of the swim-lane to move the task into. If omitted, the swimlane doesn't change
     * - position The position of the task in the new column (zero-based). If omitted, the task will be placed at the bottom of the column
     * - exceedingreason If you can exceed a limit with a reason, supply it with this parameter
     *
     * @return 1|string|false
     */
    public function moveTask($boardid, $taskid, $column, $options = array()) {
    	$d = array('boardid' => $boardid, 'taskid' => $taskid, 'column' => $column,);
    	foreach ($options as $k => $v) {
    		$d[$k] = $v;
    	}

    	$this->API->data($d);

    	$res = $this->API->call('move_task');
    	$res = $res['res'];
    	if ($res) {
    		return @$res['status'] ? : false;
    	}

    	return false;
    }
    /**
     * @name blockTask
     *
     * @param int $boardid The ID of the board where the task to be moved is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
     * @param int $taskid The ID of the task to be deleted.
     * @param string $event Possible valules:
     * - 'block' - block a task
     * - 'editblock' - edit the blocked reason
     * - 'unblock' - unblock a task
     *
     * @param string $blockreason Required if event is set to 'block' or 'editblock
     *
     * @return 1|string|false
     */
    public function blockTask($boardid, $taskid, $event, $blockreason = null) {
    	$d = array('boardid' => $boardid, 'taskid' => $taskid, 'event' => $event);
    	if ($blockreason) {
    		$d['blockreason'] = $blockreason;
    	}

    	$this->API->data($d);

    	$res = $this->API->call('block_task');
    	$res = $res['res'];
    	if ($res) {
    		return @$res['status'] ? : false;
    	}

    	return false;
    }

    /**
     * @name addComment
     *
     * @param int $taskid The ID of the task to be deleted.
     * @param string $comment
     *
     * @return array
     */
    public function addComment($taskid, $comment) {
    	$this->API->data(array(
    		'taskid' => $taskid, 
    		'comment' => $comment,
    		));
    	return $this->API->call('add_comment');
    }

    /**
     * @getProjectsAndBoards
     * @desc get_projects_and_boards method, limit 30/hour
     *
     * @return array|false
     *
     * return array of projects, each with
     * - [][name] The name of the project
     * - [][id] The ID of the project
     * - [][boards] Array of details for any boards in current project ( name, id )
     */
    public function getProjectsAndBoards () {
    	$this->API->data(array());
    	$res = $this->API->call('get_projects_and_boards');
    	if ($res) {
    		return @$res['projects'] ? : false;
    	}

    	return false;
    }

 }

// -- eof
 ?>
