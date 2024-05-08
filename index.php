<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title>Large sql update test</title>
  </head>
  <body>
<?php
function files_in_dir($path, $ext = '')
{
    $files = [];
    if (file_exists(realpath($path))) {
        $f = scandir(realpath($path));
        foreach ($f as $file) {
            if (is_dir($file)) {
                continue;
            }
            if (empty($ext)) {
                $files[] = $file;
            } else {
                $arr = explode(',', $ext);
                foreach ($arr as $value) {
                    $extt = mb_strtolower(trim($value));
                    if ($extt === mb_strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
                        $files[] = $file;
                    }
                }
            }
        }
    }

    return $files;
}
foreach (files_in_dir(__DIR__, 'php,html') as $value) {
    if (!str_contains($value, 'index.php')) {
        echo '<p><a href="'.$value.'">'.pathinfo($value, PATHINFO_FILENAME).'</a></p>';
    }
}
?>
  </body>
</html>