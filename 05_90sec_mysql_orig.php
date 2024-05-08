<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Hello!</title>
  </head>
  <body>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$start = microtime(true);
function fancyFunction1($value)
{
    return round($value * (mt_rand() / mt_getrandmax()));
}

function fancyFunction2($value)
{
    return substr($value, rand(0, strlen($value) - 1), rand(1, strlen($value)));
}

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'task1';

// Original queries. ~1.5 minutes
$connection = mysqli_connect($host, $user, $password, $dbname);

$users = $connection->query('SELECT * FROM users')->fetch_all(MYSQLI_ASSOC);

foreach ($users as $user) {
    $id = $user['id'];
    $newParam1 = fancyFunction1($user['param1']);
    $newParam2 = fancyFunction2($user['param2']);

    $connection->query("UPDATE `users` SET `users`.`param1` = '$newParam1', `users`.`param2` = '$newParam2' WHERE `users`.`id` = $id");
}
echo 'The execution time: '.number_format(microtime(true) - $start).' seconds.';
?>
  </body>
</html>
<?php
exit(0);
