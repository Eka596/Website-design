<?php
function pdo(): PDO{
  static $pdo;

  if (!$pdo){
    $configPath = __DIR__.'/config.php';

    if (!file_exists($configPath)){
      $msg = 'Конфигурационный файл не найден.';
      trigger_error($msg, E_USER_ERROR);
    }

    $config = include $configPath;

    $dsn = 'mysql:dbname='.$config['db_name'].';host='.$config['db_host'];
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  
  return $pdo;
}
?>