<?php
  function db_connect() {
    require_once 'connection.php';
    $connString = "host=" . $host . " dbname=" . $database . " user=" . $user . " password=" . $password;
    $dbconn = pg_connect($connString)
      or die('Could not connect: ' . pg_last_error());
      return $dbconn;
  }

  function db_close($db) {
    pg_close($db);
  }
?>