<?php
  // $dbconn = null;
  function db_connect() {
    require_once 'connection.php';
    $connString = "host=" . $host . " dbname=" . $database . " user=" . $user . " password=" . $password;
    // error_log("$connString: ". $connString);
    $dbconn = pg_connect($connString)
      or die('Could not connect: ' . pg_last_error());
      return $dbconn;
  }

  function db_close($db) {
    pg_close($db);
  }

?>