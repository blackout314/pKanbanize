# pKanbanize
A nice class to call kanbanize API from PHP


##Basic usage:
```php
$kanbanize = new pKanbanizeApi('email', 'password');
$projects = $kanbanize->getProjectsAndBoards();
echo '<pre>';
print_r($projects);
echo '</pre>';
```

##Pro users - kanbanize via subdomain

If you access kanbanize on your own subdomain, you can specify the subdomain name in the constructor.

https://yourcompany.kanbanize.com/

```php
$kanbanize = new pKanbanizeApi('email', 'password', 'yourcompany');

```
##TEAM

[ ![Carlo Denaro avatar](http://www.carlodenaro.com/me.jpg) Carlo Denaro ](https://github.com/blackout314)
[ ![Carlo Panzi avatar](https://avatars1.githubusercontent.com/u/51404?s=100) Carlo Panzi ](https://github.com/kajyr)
