<?php

include 'pKanbanizeApi.class.php';

$kanbanize = new pKanbanizeApi('email', 'password', 'yoox');



// retrieve info
$projects = $kanbanize->getProjectsAndBoards();
echo '<pre>';
print_r($projects);
echo '</pre>';


// retrieve info
$tasks = $kanbanize->getAllTasks(229);
echo '<pre>';
print_r($tasks);
echo '</pre>';



?>
