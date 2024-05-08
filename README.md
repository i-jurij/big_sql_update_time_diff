# A case of methods of update a large number of rows   
Numbered approximately by increasing the execution time,    
indicating the approximate execution time on a dual-core    
Intel(R) Core(TM) i3-8130U CPU @ 2.20GHz.   
## 1. Create db and import data
Run sql server (in this case mariadb or mysql)   
and create db with name "task1"   
and import data from file "users.sql".    

Or if you use Linux and XAMMP or own linux server with sql user "root"   
and "password=''" simple run   
[create_test_db](http://localhost/create_test_db.php)

## 2. Run PHP local server
Run 
```sh
php -S localhost:8000
```   
   
in folder "big_sql_update_time_diff"
and select one of the scripts.  

Or only for one selecting script
```sh
php -S 127.0.0.1:8000 script_name.php
```
## 3. Open page in browser
After executing the script, the approximate execution time   
will be indicated on the page opened on url [localhost:8000](http://localhost:8000)

## 4. Useful information
    Get only the necessary ones from DB (use columns names, WHERE eg).   
    Use a PDO prepared statement, as prepared queries do not require any checking.   

    При импорте данных и вставке больших объемов информации в таблицы InnoDB   
    следует отключать autocommit и фиксировать изменения после совершения группы запросов.   
    In PHP PDO: PDO->beginTrasaction(); PDO->commit; (PDO->rollback;)  

    Update a large number of rows (ex >1000) iteratively,   
    only a portion until the update is complete,   
    because TiDB limits the size of a single transaction (txn-total-size-limit,   
    100 MB by default).   
    Too many data updates at once will result in holding locks for too long   
    (pessimistic transactions), or causing conflicts (optimistic transactions).   
    You can use a loop in your program or script to complete the operation.   
    
    For INSERT ... ON DUPLICATE KEY: you should try to avoid using an    
    ON DUPLICATE KEY UPDATE clause on tables with multiple unique indexes,   
    because If unic1=1 OR unic2=2 matches several rows, only one row is updated.   
    
    Используйте индексирование по столбцам с уникальными, редко изменяемыми значениями.  
