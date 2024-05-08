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

// config
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'task1';

// connect via PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Подключение не удалось: '.$e->getCode().'|'.$e->getMessage();
    exit;
}

// 1. PDO transaction with prepare queries. Quickly: ~7 seconds
try {
    $stmt = $pdo->query('SELECT `id`, `param1`, `param2` FROM `users`');
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // update data
    $query = 'UPDATE `users` SET `param1` = :param1, `param2` = :param2 WHERE `id` = :id';
    $stmt = $pdo->prepare($query);
    // Start Transaction
    $pdo->beginTransaction();
    // get array with data for update
    foreach ($res as $key => $value) {
        $p1 = fancyFunction1($value['param1']);
        $p2 = fancyFunction2($value['param2']);

        $stmt->bindParam(':id', $value['id'], PDO::PARAM_INT);
        $stmt->bindParam(':param1', $p1, PDO::PARAM_INT);
        $stmt->bindParam(':param2', $p2, PDO::PARAM_STR);

        $stmt->execute();
    }
} catch (PDOException $e) {
    echo 'PDOException: '.$e->getCode().'|'.$e->getMessage();
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        $pdo = null;
    }
    exit;
}

if (isset($pdo) && $pdo->inTransaction()) {
    // commit
    $pdo->commit();
    // Close connection
    $pdo = null;
    echo 'The execution time: '.number_format(microtime(true) - $start).' seconds.';
}
?>

  </body>
</html>
<?php

exit(0);
