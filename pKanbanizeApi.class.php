<?php
include_once 'pKanbanizeApiCall.class.php';
/**
 * @name pKanbanizeApi
 * @desc connect to kanbanize api
 */
class pKanbanizeApi
{
	/**
	 * @var string YOUR API CALL
	 */
	protected $api_key;
	protected $email;
	protected $password;

	protected $base_kanbanize_url = 'kanbanize.com/index.php/api/kanbanize';

	protected $kanbanize_url;

	function __construct($email, $password, $domain = '') {

		if ($domain != '') {
			$domain = $domain . '.';
		}

		$this->kanbanize_url = 'http://'.$domain.$this->base_kanbanize_url;

		$this->email = $email;
		$this->password = $password;
	}

	/**
	 * @name setApiKey
	 * @param string $k
	 *
	 * @return $this
	 */
	public function setApiKey($k)
	{
		$this->api_key = $k;
		return $this;
	}

	protected function executeCall(pKanbanizeApiCall $call)
	{
		
		if ($call->function != 'login' && empty($this->apy_key)) {
			$l = $this->login();
			$this->setApiKey($l['apikey']);
		}

		$api_key = $this->api_key;

		$function = $call->function;
		$format   = $call->format;

		$url = $this->kanbanize_url;
		$url .= "/$function";

		if ($format) {
			$url .= "/format/$format";
		}

		//# headers and data (this is API dependent, some uses XML)
		$headers = $call->headers; //array('Accept: application/json');
		$data    = $call->data; //array('firstName' => 'John', 'lastName' => 'Doe');

		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $url);

		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));

		$headers[] = "apikey: $api_key";
		$headers[] = "Content-type: application/json; charset=utf-8";

		curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($handle);

		$call->request_error = curl_error($handle);

		$call->response = $response;

		$call->response_code = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);

		curl_close($handle);
		return $call;
	}

	protected function doCall(pKanbanizeApiCall $call)
	{
		$call = $this->executeCall($call);
		if ($call->request_error) {
			throw new Exception('problem with call: ' . $call->request_error);
		}

		return $call->getResponseDecoded();
	}

	/**
	 * @name login
	 * @desc login method. Limit 30/hour
	 *
	 *
	 * @return array
	 * with data:
	 * - email       Your email address
	 * - username    Your username
	 * - realname    Your name
	 * - companyname Company name
	 * - timezone    Your time zone
	 * - apykey      Your API key.
	 */
	protected function login()
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('login');
		$call->setData(array('email' => $this->email, 'pass' => $this->password));

		return $this->doCall($call);
	}

	/**
	 * @name createNewTask
	 * @desc create_new_task method. Limit 30/hour
	 *
	 * @param int   $boardid The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param array $data
	 *
	 * - title       Title of the task
	 * - description Description of the task
	 * - priority    One of the following: Low, Average, High
	 * - assignee    Username of the assignee (must be a valid username)
	 * - color       Any color code (e.g. 99b399) DO NOT PUT the # sign in front of the code!!!
	 * - size        Size of the task
	 * - tags        Space separated list of tags
	 * - deadline    Dedline in the format: yyyy-mm-dd (e.g. 2011-12-13)
	 * - extlink     A link in the following format: https:\\github.com\philsturgeon. If the parameter is embedded in the request BODY
	 * - type        The name of the type you want to set.
	 * - template    The name of the template you want to set. If you specify any property as part of the request, the one specified in the template will be overwritten.
	 *
	 * @return int|null
	 */
	public function createNewTask($boardid, $data = array())
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('create_new_task');

		$d = array();
		foreach ($data as $k => $v) {
			$d[$k] = $v;
		}

		$d['boardid'] = $boardid;

		$call->setData($d);

		$resp = $this->doCall($call);
		if ($resp) {
			return @$resp['taskid'] ? : @$resp['id'] ? : null;
		}

		return null;
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
	public function getBoardSettings($boardid)
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('get_board_settings');

		$call->setData(array('boardid' => $boardid));

		return $this->doCall($call);
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
	public function getBoardStructure($boardid)
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('get_board_structure');

		$call->setData(array('boardid' => $boardid));

		return $this->doCall($call);
	}

	/**
	 * @getProjectsAndBoards
	 * @desc get_projects_and_boards method, limit 30/hour
	 *
	 * @return array|null
	 *
	 * return array of projects, each with
	 * - [][name]    The name of the project
	 * - [][id]    The ID of the project
	 * - [][boards]    Array of details for any boards in current project ( name, id )
	 */
	public function getProjectsAndBoards()
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('get_projects_and_boards');

		$resp = $this->doCall($call);
		if ($resp) {
			return @$resp['projects'] ? : null;
		}

		return null;
	}

	/**
	 * @name deleteTask
	 *
	 * @param int $boardid The ID of the board you want the new task created into. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int $taskid The ID of the task to be deleted.
	 *
	 * @return 1|string|null
	 */
	public function deleteTask($boardid, $taskid)
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('delete_task');

		$call->setData(array('boardid' => $boardid, 'taskid' => $taskid,));

		$resp = $this->doCall($call);
		if ($resp) {
			return @$resp['status'] ? : null;
		}

		return null;
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
	public function getTaskDetails($boardid, $taskid, $history = false)
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('get_task_details');

		$d = array('boardid' => $boardid, 'taskid' => $taskid,);

		if ($history) {
			$d['history'] = 'yes';
		}

		$call->setData($d);

		return $this->doCall($call);
	}

	/**
	 * @name addComment
	 *
	 * @param int    $taskid The ID of the task to be deleted.
	 * @param string $comment
	 *
	 * @return array
	 */
	public function addComment($taskid, $comment)
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('add_comment');

		$d = array('taskid' => $taskid, 'comment' => $comment,);
		$call->setData($d);

		return $this->doCall($call);
	}

	/**
	 * @name moveTask
	 * @desc move_task method. Limit 60/hour
	 *
	 * @param int    $boardid The ID of the board where the task to be moved is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int    $taskid The ID of the task to be deleted.
	 * @param string $column The name of the column to move the task into. If the name of the column is unique, you can specify it alone, but if there are more than one columns with that name, you must specify it as columnname1 . columnname2 . columnname3
	 *
	 * @param array  $options
	 *
	 * - lane            The name of the swim-lane to move the task into. If omitted, the swimlane doesn't change
	 * - position        The position of the task in the new column (zero-based). If omitted, the task will be placed at the bottom of the column
	 * - exceedingreason If you can exceed a limit with a reason, supply it with this parameter
	 *
	 * @return 1|string|null
	 */
	public function moveTask($boardid, $taskid, $column, $options = array())
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('move_task');

		$d = array('boardid' => $boardid, 'taskid' => $taskid, 'column' => $column,);

		foreach ($options as $k => $v) {
			$d[$k] = $v;
		}

		$call->setData($d);

		$resp = $this->doCall($call);
		if ($resp) {
			return @$resp['status'] ? : null;
		}

		return null;
	}

	/**
	 * @name blockTask
	 *
	 * @param int    $boardid The ID of the board where the task to be moved is located. You can see the board ID on the dashboard screen, in the upper right corner of each board.
	 * @param int    $taskid The ID of the task to be deleted.
	 * @param string $event Possible valules:
	 * - 'block' - block a task
	 * - 'editblock' - edit the blocked reason
	 * - 'unblock' - unblock a task
	 *
	 * @param string $blockreason Required if event is set to 'block' or 'editblock
	 *
	 * @return 1|string|null
	 */
	public function blockTask($boardid, $taskid, $event, $blockreason = null)
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('block_task');

		$d = array('boardid' => $boardid, 'taskid' => $taskid, 'event' => $event);

		if ($blockreason) {
			$d['blockreason'] = $blockreason;
		}

		$call->setData($d);

		$resp = $this->doCall($call);
		if ($resp) {
			return @$resp['status'] ? : null;
		}

		return null;
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
	 * @return 1|string|null
	 */
	public function editTask($boardid, $taskid, $changeData = array())
	{
		$call = new pKanbanizeApiCall();
		$call->setFunction('edit_task');

		$d = array('boardid' => $boardid, 'taskid' => $taskid);

		foreach ($changeData as $k => $v) {
			$d[$k] = $v;
		}

		$call->setData($d);

		$resp = $this->doCall($call);
		if ($resp) {
			return @$resp['status'] ? : null;
		}

		return null;
	}

	/**
	 * @name getAllTasks
	 *
	 * @param {int} boardid
	 *
	 * @return {Array} all tasks
	 */
	public function getAllTasks($boardid)
	{
		$call = new pKanbanizeApiCall();

		$call->setFunction('get_all_tasks');
		$call->setData(array('boardid' => $boardid));

		return $this->doCall($call);
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
	public function getBoardActivities( $boardid, $fromdate, $todate, $page=1, $resultsperpage=30, $author=null, $eventtype=null, $textformat='plain') 
	{
		$call = new pKanbanizeApiCall();

		$call->setFunction('get_board_activities');
		$call->setData( array(
			'boardid' => $boardid, 
			'fromdate' => $fromdate, 
			'todate' => $todate,
			'page' => $page,
			'resultsperpage' => $resultsperpage,
			'author' => $author,
			'eventtype' => $eventtype,
			'textformat' => $textformat
			) );

		return $this->doCall($call);
	}

}
// -- class end

// -- eof
?>
