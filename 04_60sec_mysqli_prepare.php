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

// mysqli prepare queries. (from 55 seconds to 1,6 minute)
$connection = new mysqli($host, $user, $password, $dbname);

$users = $connection->query('SELECT * FROM users')->fetch_all(MYSQLI_ASSOC);

$query = 'UPDATE `users` SET `users`.`param1` = ?, `users`.`param2` = ? WHERE `users`.`id` = ?';
$types = 'isi';

if ($stmt = $connection->prepare($query)) {
    foreach ($users as $user) {
        $parameters = [fancyFunction1($user['param1']), fancyFunction2($user['param2']), $user['id']];
        $stmt->bind_param($types, ...$parameters);
        $stmt->execute();
        // echo $stmt->affected_rows.'<br>';
    }
    $stmt->close();
}
$connection->close();
echo 'The execution time: '.number_format(microtime(true) - $start).' seconds.';
?>
  </body>
</html>
<?php
exit(0);
