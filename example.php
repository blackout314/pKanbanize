<?php


include 'class.pkanbanize.php';


$kanbanize = new \Lib\pkanbanize( null, DOMAIN, USER, PASS );


// retrieve info
$projects = $kanbanize->getProjectsAndBoards();
echo '<pre>';
print_r($projects['res']);
echo '</pre>';


// retrieve info
$tasks = $kanbanize->getAllTasks(229);
echo '<pre>';
print_r($tasks['res']);
echo '</pre>';


?>