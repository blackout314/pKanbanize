<?php

// define some const
//
define( KANBANIZE_API_KEY, 'YOUR-API-KEY' );
define( USERNAME, 'username' );
define( PASSWORD, 'password' );

// include libs
//
include 'API.php';
include 'APICall.php';

// login to api
//
pKanbanizeApi::getInstance()->setApiKey( KANBANIZE_API_KEY );
pKanbanizeApi::getInstance()->login( USERNAME, PASSWORD );

// retrieve info
//
echo print_r( pKanbanizeApi::getInstance()->getAllTasks( $TASK ) );

?>
