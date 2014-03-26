# pKanbanize
A nice class to call kanbanize API from PHP


##Basic usage:
```php
$kanbanize = new pKanbanizeApi('email', 'password', 'yoox');
$projects = $kanbanize->getProjectsAndBoards();
echo '<pre>';
print_r($projects);
echo '</pre>';
```