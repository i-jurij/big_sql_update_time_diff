<?php

$host = 'localhost';
$user = 'root';
$password = '';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password, $options);
} catch (PDOException $e) {
    echo 'No connection: '.$e->getCode().'|'.$e->getMessage().PHP_EOL;
    exit;
}

// Create database function
function createDb(PDO $pdo)
{
    $sql = 'CREATE DATABASE `task1`';
    $pdo->query($sql);
    echo 'Database "task1" created successfully.<br>';
}

function importTable()
{
    $filename = __DIR__.'/users.sql';
    $filename.'<br>';

    echo '<pre>';
    $last_line = system("/opt/lampp/bin/mysql --user=root --password= task1 < $filename", $retval);
    echo '
    </pre>
    <hr />Last line of the output: '.$last_line.'<hr />';
    switch ($retval) {
        case 0:
            print 'Success! Data imported.<br>';
            break;
        case 1:
            print 'There was an error during import.<br>';
            break;
        case 2:
            print 'File <b>'.$filename.'</b> not found.<br>';
            break;
        case 127:
            print 'Use full path to mysql eg <code>/opt/lampp/bin/mysql</code>';
            break;
    }
}

function checkAndImportTable(PDO $pdo)
{
    $table_isset = $pdo->query("SHOW TABLES FROM `task1` LIKE 'users%'");
    echo '<hr />';
    $te = $table_isset->fetch(PDO::FETCH_NUM);
    if (!empty($te[0])) {
        echo 'You already have a table "users"';
    } else {
        importTable();
    }
}

try {
    $pdo->exec('USE `task1`');
    echo 'Connect to database "task1".<br>';
    checkAndImportTable($pdo);
} catch (PDOException $e) {
    try {
        createDb($pdo);
        checkAndImportTable($pdo);
    } catch (\Throwable $th) {
        echo $th->getCode().'|'.$th->getMessage().PHP_EOL;
        exit;
    }
}

$pdo = null;

/*
$maxRuntime = 8; // less then your max script execution limit

$deadline = time() + $maxRuntime;
$progressFilename = $filename.'_filepointer'; // tmp file for progress
$errorFilename = $filename.'_error'; // tmp file for error

($fp = fopen($filename, 'r')) or exit('failed to open file:'.$filename);

// check for previous error
if (file_exists($errorFilename)) {
    exit('<pre> previous error: '.file_get_contents($errorFilename));
}

// activate automatic reload in browser
echo '<html><head> <meta http-equiv="refresh" content="'.($maxRuntime + 2).'"><pre>';

// go to previous file position
$filePosition = 0;
if (file_exists($progressFilename)) {
    $filePosition = file_get_contents($progressFilename);
    fseek($fp, $filePosition);
}

$queryCount = 0;
$query = '';
while ($deadline > time() and ($line = fgets($fp, 1024000))) {
    if (substr($line, 0, 2) == '--' or trim($line) == '') {
        continue;
    }

    $query .= $line;
    if (substr(trim($query), -1) == ';') {
        $igweze_prep = $pdo->prepare($query);

        if (!$igweze_prep->execute()) {
            $error = 'Error performing query \'<strong>'.$query.'\': '.print_r($pdo->errorInfo());
            file_put_contents($errorFilename, $error."\n");
            exit;
        }
        $query = '';
        file_put_contents($progressFilename, ftell($fp)); // save the current file position for
        ++$queryCount;
    }
}

if (feof($fp)) {
    echo 'dump successfully imported!';
} else {
    echo ftell($fp).'/'.filesize($filename).' '.(round(ftell($fp) / filesize($filename), 2) * 100).'%'."\n";
    echo $queryCount.' queries processed! please reload or wait for automatic browser refresh!';
}
*/
exit(0);
