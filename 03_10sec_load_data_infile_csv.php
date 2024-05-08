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

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
       // PDO::MYSQL_ATTR_LOCAL_INFILE => true,
    ];
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, $options);
} catch (PDOException $e) {
    echo 'Подключение не удалось: '.$e->getCode().'|'.$e->getMessage().PHP_EOL;
    exit;
}
// 4. Insert data from csv file, ~10 seconds
try {
    // $stmt = $pdo->query('SELECT `id`, `param1`, `param2` FROM `users`');
    $stmt = $pdo->query('SELECT `name`, `age`, `param1`, `param2`, `param3` FROM `users`');
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $key => $value) {
        foreach ($value as $k => $v) {
            if ($k === 'param1') {
                $v = fancyFunction1($v);
            } elseif ($k === 'param2') {
                $v = fancyFunction2($v);
            }
            $new[$key][$k] = $v;
        }
    }
    $file = __DIR__.'/file.csv';

    if (!empty($new)) {
        if (!$buffer = fopen($file, 'w')) {
            throw new Exception("Could not open $file for writing.".PHP_EOL);
            exit;
        }

        fputs($buffer, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));
        foreach ($new as $val) {
            fputcsv($buffer, $val, ',', '"', '\\', PHP_EOL);
        }
        fclose($buffer);
    } else {
        echo 'DB table is empty'.PHP_EOL;
    }

    if ($file !== false) {
        $sql = "TRUNCATE `users`;
            LOAD DATA INFILE '$file' 
            REPLACE INTO TABLE `users` 
            FIELDS TERMINATED BY ','
            (name, age, param1, param2, param3);";
        $pdo->beginTransaction();
        $stmt = $pdo->query($sql);
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
    $pdo->commit();
    $pdo = null;
}
echo 'The execution time: '.number_format(microtime(true) - $start).' seconds.';
?>
  </body>
</html>
<?php
    exit;
