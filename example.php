<?php

define( KANBANIZE_API_KEY, 'API' );
define( USERNAME, 'username' );
define( PASSWORD, 'password' );

include 'API.php';
include 'APICall.php';

EtuDev_KanbanizePHP_API::getInstance()->setApiKey( KANBANIZE_API_KEY );
EtuDev_KanbanizePHP_API::getInstance()->login( USERNAME, PASSWORD );

//echo print_r( EtuDev_KanbanizePHP_API::getInstance()->getBoardSettings( 6 ) );
echo print_r( EtuDev_KanbanizePHP_API::getInstance()->getAllTasks( 6 ) );

?>
