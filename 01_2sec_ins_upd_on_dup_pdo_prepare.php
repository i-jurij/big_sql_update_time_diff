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

// pdo prepare chunk insert update on duplicate key.
// chunk 5000 rows ~1.95 seconds!!!
// chunk 1000 rows ~2 seconds!!!
// BUT ROWS ARE LOST! WHY?
function updateData(PDO $pdo, string $table, array $data)
{
    $chunk = array_chunk($data, 5000);

    foreach ($chunk as $value) {
        $length = count($value);
        $questionmarks = str_repeat('(?,?,?,?,?,?),', $length - 1).'(?,?,?,?,?,?)';
        $i = 0;
        $rows = [];

        foreach ($value as $data) {
            $rows[$i] = $data['id'];
            $rows[++$i] = $data['name'];
            $rows[++$i] = $data['age'];
            $rows[++$i] = $data['param1'];
            $rows[++$i] = $data['param2'];
            $rows[++$i] = $data['param3'];
            ++$i;
        }

        $types = str_repeat('isiiss', count($value));

        if ($stmt = $pdo->prepare('
                    INSERT INTO users (id, name, age, param1, param2, param3)
                    VALUES '.$questionmarks.'
                    ON DUPLICATE KEY UPDATE
                        name = VALUE(name),
                        age = VALUE(age),
                        param1 = VALUE(param1),
                        param2 = VALUE(param2),
                        param3 = VALUE(param3)')) {
            $stmt->execute($rows);
        }
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
    $stmt = $pdo->query('SELECT `id`,`name`, `age`,`param1`,`param2`,`param3` FROM users');
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($res as $key => $user) {
        $res[$key]['param1'] = fancyFunction1($user['param1']);
        $res[$key]['param2'] = fancyFunction2($user['param2']);
    }
    $pdo->beginTransaction();
    echo updateData($pdo, 'users', $res);
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
