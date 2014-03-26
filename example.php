<?php

include 'pKanbanizeApi.class.php';

$kanbanize = new pKanbanizeApi('user', 'password', 'domain'); // sarebbe meglio il nome della classe maiuscolo


$login = $kanbanize->login('user', 'password');

$tasks = $kanbanize->getAllTasks(229);

echo '<pre>';
print_r($login);
echo '</pre>';
// login to api
//
//pKanbanizeApi::getInstance()->setApiKey( KANBANIZE_API_KEY );
//pKanbanizeApi::getInstance()->login( USERNAME, PASSWORD );

// retrieve info
//
//echo print_r( pKanbanizeApi::getInstance()->getAllTasks( $TASK ) );

?>
