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

// pdo prepare chunk case when then.
// chunk 10000 rows ~14.5 seconds
// chunk 5000 rows ~5.0 seconds !!
// chunk 1000 rows ~2.3 seconds !!!
// chunk 500 rows ~2.0 seconds !!!
// BUT ROWS ARE LOST! WHY?
function updateData(PDO $pdo, string $table, array $data)
{
    $chunk = array_chunk($data, 1000);

    foreach ($chunk as $value) {
        $length = count($value);
        $case_thens = str_repeat('WHEN ? THEN ? ', $length);
        $questionmarks = str_repeat('?,', $length - 1).'?';
        $stmt = $pdo->prepare(
            'UPDATE `'.$table.'`
                SET `param1` = CASE `id`
                                    '.$case_thens.' END,
                    `param2` = CASE `id`
                                    '.$case_thens.' END
                WHERE id IN('.$questionmarks.')');

        $i = 1;
        // Подставление значений в WHEN ? THEN ? for param1
        foreach ($value as $user) {
            $stmt->bindValue($i, $user['id'], PDO::PARAM_INT);
            ++$i;
            $p1 = fancyFunction1($user['param1']);
            $stmt->bindValue($i, $p1, PDO::PARAM_INT);
            ++$i;
        }
        // Подставление значений в WHEN ? THEN ? for param2
        foreach ($value as $user) {
            $stmt->bindValue($i, $user['id'], PDO::PARAM_INT);
            ++$i;
            $p2 = fancyFunction2($user['param2']);
            $stmt->bindValue($i, $p1, PDO::PARAM_STR);
            ++$i;
        }
        // Подставление значений в IN(?, ?, ?, ?)
        foreach ($value as $user) {
            $stmt->bindValue($i, $user['id'], PDO::PARAM_INT);
            ++$i;
        }

        $stmt->execute();
    }
}

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'task1';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_LOCAL_INFILE => true,
    ];
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, $options);
} catch (PDOException $e) {
    echo 'Подключение не удалось: '.$e->getCode().'|'.$e->getMessage().PHP_EOL;
    exit;
}

try {
    $stmt = $pdo->query('SELECT `id`, `param1`, `param2` FROM `users`');
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo->beginTransaction();
    updateData($pdo, 'users', $res);
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
