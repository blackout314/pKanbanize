# pKanbanize v 2.0
A nice class to call [kanbanize](http://kanbanize.com ) API from PHP


## Basic usage:
```php
$kanbanize = new \Lib\pkanbanize( YOURKEY, DOMAIN ); 
// or
$kanbanize = new \Lib\pkanbanize( null, DOMAIN, USER, PASS );

$tasks = $kanbanize->getAllTasks( IDBOARD )
echo '<pre>';
print_r($tasks);
echo '</pre>';
```

## Available functions

You can refer to the official [kanbanize API doc](http://kanbanize.com/ctrl_integration)

Implemented function

+ getProjectsAndBoards
+ getBoardStructure
+ getBoardSettings
+ getBoardActivities
+ createNewTask
+ deleteTask
+ getTaskDetails
+ getAllTasks
+ addComment
+ moveTask
+ editTask
+ blockTask
+ getLinks


## Pro users - kanbanize via subdomain

If you access kanbanize on your own subdomain, you can specify the subdomain name in the constructor.

https://yourcompany.kanbanize.com/

```php
$kanbanize = new \Lib\pkanbanize( YOURKEY, DOMAIN ); 
```
## TEAM

[ ![Carlo Denaro avatar](http://www.carlodenaro.com/me.jpg) Carlo Denaro ](https://github.com/blackout314)
[ ![Carlo 'kajyr' avatar](https://avatars1.githubusercontent.com/u/51404?s=200) Carlo 'kajyr' ](https://github.com/kajyr)
