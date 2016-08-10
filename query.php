<?php
require_once 'postgresql.php';
//Разбираем входящий пост запрос
  $q = $_POST["q"];
  $d1 = $_POST["d1"];
  $d2 = null;
  if (isset($_POST["d2"])) {
      $d2 = $_POST["d2"];
  }

 
  $q = str_replace('\"', '\\"', $q);
  $d1 = str_replace('\"', '\\"', $d1);
  $d2 = str_replace('\"', '\\"', $d2);
  
  $outData = array();
  $db = db_connect();
  switch ($q) {
    case "getData":
      $outData = getData($d1);
      break;
    case "getDataCur":
      $outData = getDataCur($d1, $d2);
      break;
  }
  db_close($db);

  echo json_encode($outData);

  function getData($curDate) {
    $queryDate = DateTime::createFromFormat('Y-m-d', $curDate)->format('Y-m');
    $tempArray = [];
    $i = 0;
    //Получаем ид Аптеки, продажи карт.опт, продажи розн.опт, остаток.опт
    $query = "SELECT \"aptID\", \"cardSellingWholesale\", \"cashSellingWholesale\", \"invoiceBalanceWholesale\", \"reportEndDate\"
    FROM \"AptData\" 
    WHERE \"reportEndDate\" IN (
      SELECT MAX(\"reportEndDate\") FROM \"AptData\" WHERE
        EXTRACT(YEAR FROM \"reportEndDate\") = '". explode("-", $queryDate)[0] ."' AND 
        EXTRACT(MONTH FROM \"reportEndDate\") = '". explode("-", $queryDate)[1] ."' 
    )
    ORDER BY \"aptID\";";
    // error_log("MAX QUERY: ". $query);
    $result = pg_query($query) or die('Query failed: ' . pg_last_error());

    while ($line = pg_fetch_array($result, null, PGSQL_NUM)) {
      $queryDate = $line[4];
      $aptId = $line[0];
      //Получаем название аптеки
      $query = "SELECT \"aptName\" FROM \"AptCatalog\" WHERE \"id\" = '" . $aptId . "';";
      $AptNameresult = pg_query($query) or die('Query failed: ' . pg_last_error());
      $AptName = pg_fetch_array($AptNameresult, null, PGSQL_NUM)[0];
      pg_free_result($AptNameresult);

      //Дата
      $tempArray[$i]["Test"] = $line[4];
      $tempArray[$i]["Date"] = $queryDate;
      //Название Аптеки
      $tempArray[$i]["AptName"] = $AptName;
      $tempArray[$i]["AptId"] = $aptId;
      //Получаем продажи
      $query = "SELECT \"wholesale\" FROM \"SupplData\" WHERE \"aptId\" = '" . $aptId . "' AND \"reportDate\" = '" . $queryDate ."' AND \"supplName\" = 'Медилон-Фармимэкс';";
      $SupplDataResult = pg_query($query) or die('Query failed: ' . pg_last_error());
      $supLine = pg_fetch_array($SupplDataResult, null, PGSQL_NUM);

      //Медилон
      $tempArray[$i]["Medilon"] = ($supLine) ? $supLine[0] : 0.0;
      pg_free_result($SupplDataResult);

      $query = "SELECT \"wholesale\" FROM \"SupplData\" WHERE \"aptId\" = '" . $aptId . "' AND \"reportDate\" = '" . $queryDate ."' AND \"supplName\" = 'Катрен';";
      $SupplDataResult = pg_query($query) or die('Query failed: ' . pg_last_error());
      $supLine = pg_fetch_array($SupplDataResult, null, PGSQL_NUM);

      //Катрен
      $tempArray[$i]["Katren"] = ($supLine) ? $supLine[0] : 0.0;
      pg_free_result($SupplDataResult);

      $query = "SELECT \"wholesale\" FROM \"SupplData\" WHERE \"aptId\" = '" . $aptId . "' AND \"reportDate\" = '" . $queryDate ."' AND \"supplName\" = 'Протек';";
      $SupplDataResult = pg_query($query) or die('Query failed: ' . pg_last_error());
      $supLine = pg_fetch_array($SupplDataResult, null, PGSQL_NUM);

      //Протек
      $tempArray[$i]["Protek"] = ($supLine) ? $supLine[0] : 0.0;
      pg_free_result($SupplDataResult);

      //Выручка
      $tempArray[$i]["Viruchka"] = floatval($line[1]) + floatval($line[2]);
      //Остаток
      $tempArray[$i]["Ostatok"] = $line[3];
      $i++;
    }
    pg_free_result($result);
    return $tempArray;

  }

  function getDataCur($aptId, $curMonth) {
    $tempArray = [];
    $queryDate = date("t/m/Y", strtotime($curMonth));
    $i = 0;

    $query = "SELECT \"reportEndDate\", \"cardSellingWholesale\", \"cashSellingWholesale\", \"invoiceBalanceWholesale\" FROM \"AptData\" WHERE \"aptID\"= " . $aptId . " AND \"reportEndDate\" <= '" . $queryDate . "' AND \"reportStartDate\" = '" . "01/" . substr($queryDate, 3) . "' ORDER BY \"reportEndDate\";";

    $result = pg_query($query) or die('Query failed: ' . pg_last_error());
    while ($line = pg_fetch_array($result, null, PGSQL_NUM)) {
      $tempArray[$i]["Date"] = $line[0];
      $tempArray[$i]["Ostatok"] = floatval($line[3]);
      $tempArray[$i]["Viruchka"] = floatval($line[1]) + floatval($line[2]);
      $i++;
    }
    pg_free_result($result);
    return $tempArray;
  }
?>
