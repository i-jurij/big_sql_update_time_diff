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

// 2 queries, 1 - select, 2 - set with case. Very slowly: 3.5 minutes and not rollback.
try {
    // get only the necessary ones
    $stmt = $pdo->query('SELECT `id`, `param1`, `param2` FROM `users`');
    $res = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

    $length = count($res);
    // prepare query string
    $sql_query = 'UPDATE `users` SET `param1` = CASE id';
    foreach ($res as $id => $value) {
        $sql_query .= ' WHEN '.$id.' THEN '.fancyFunction1($value['param1']);
    }
    $sql_query .= ' ELSE `param1` END, `param2` = CASE id';
    $ids_string = '';
    foreach ($res as $id => $value) {
        $sql_query .= ' WHEN '.$id.' THEN '.$pdo->quote(fancyFunction2($value['param2']));
        $ids_string .= $id.',';
    }
    $sql_query .= ' ELSE `param2` END WHERE `id` IN ('.rtrim($ids_string, ',').')';
    // Start Transaction
    $pdo->beginTransaction();
    $pdo->query($sql_query);
} catch (PDOException $e) {
    echo 'PDOException: '.$e->getCode().'|'.$e->getMessage();
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        $pdo = null;
    }
    exit;
}

if (isset($pdo) && $pdo->inTransaction()) {
    $pdo->commit();
    $pdo = null;
    echo 'The execution time: '.number_format(microtime(true) - $start).' seconds.';
}
?>
  </body>
</html>
<?php
    exit;
