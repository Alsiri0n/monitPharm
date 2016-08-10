<?php

// Каталог, в который мы будем принимать файл:
$uploaddir = '/srv/samba/work/WWW/ap25/uploads/';
// $uploaddir = '/srv/samba/work/WWW/ap25/uploads/';
// $uploadfile = $uploaddir.basename($_FILES['uploadfile']['name']);
$uploadfile = $_FILES['uploadfile']['tmp_name'];


//to-do Переделать парс из темп файла, не сохраняя его.
// var_dump($_FILES);
// echo ("<br>uploaddir: ". $uploaddir . "<br>");
// echo ("uploadfile: ". $uploadfile . "<br>");
// Копируем файл из каталога для временного хранения файлов:
// if (copy($_FILES['uploadfile']['tmp_name'], $uploadfile))
// {
echo "<h3>Файл успешно загружен на сервер</h3>";
// }
// else {
//   // echo "<h3>Ошибка! Не удалось загрузить файл на сервер!</h3>"; exit; 
// }

// Выводим информацию о загруженном файле: DEBUG INFO
// echo "<h3>Информация о загруженном на сервер файле: </h3>";
// echo "<p><b>Оригинальное имя загруженного файла: ".$_FILES['uploadfile']['name']."</b></p>";
// echo "<p><b>Mime-тип загруженного файла: ".$_FILES['uploadfile']['type']."</b></p>";
// echo "<p><b>Размер загруженного файла в байтах: ".$_FILES['uploadfile']['size']."</b></p>";
// echo "<p><b>Временное имя файла: ".$_FILES['uploadfile']['tmp_name']."</b></p>";

$ENCLOSUREF = "\"";
$OFFSET1 = 1;
$OFFSET3 = 3;
$OFFSET4 = 4;
$OFFSET5 = 5;
$OFFSET8 = 8;
$OFFSET9 = 9;
$OFFSET11 = 11;

//Устанавливаем отступы в зависимости от типа файла 15
if ($_FILES['uploadfile']['name'] === "15.csv" OR $_FILES['uploadfile']['name'] === "54.csv" OR $_FILES['uploadfile']['name'] === "60.csv" OR $_FILES['uploadfile']['name'] === "61.csv" OR $_FILES['uploadfile']['name'] === "66.csv") {

// if ($_FILES['uploadfile']['name'] === "15.csv" OR $_FILES['uploadfile']['name'] === "54.csv" OR $_FILES['uploadfile']['name'] === "60.csv" OR $_FILES['uploadfile']['name'] === "61.csv" OR $_FILES['uploadfile']['name'] === "66.csv") {
  echo "Устанавливаем отступы";
  // $ENCLOSUREF = "\"";
  $OFFSET1 = 1;
  
  $OFFSET3 = 4;
  
  $OFFSET4 = 5;
  $OFFSET5 = 6;

  $OFFSET8 = 9;
  $OFFSET9 = 10;
  $OFFSET11 = 13;
}

$row = 0;
$f = file_get_contents($uploadfile);
// $f = file_get_contents($_FILES['uploadfile']['tmp_name']);
$f = iconv("WINDOWS-1251", "UTF-8", $f);
file_put_contents($uploadfile, $f);
// file_put_contents($_FILES['uploadfile']['tmp_name'], $f);
$aptName = "Неопределенная аптека"; // Название аптеки
$reportType = "ТОВАРНЫЙ ОТЧЕТ"; // Тип загружаемого отчёта
$createTime = "01.01.01, 00:00:01"; // Дата создания отчёта
$reportStartDate = "01/01/01"; // Начало отчетного периода
$reportEndDate = "01/01/01"; // Конец отчетного периода
$forwardBalanceRetail = 0.0; // Входящий остаток в розн.ценах
$forwardBalanceWholesale = 0.0; // Входящий остаток в закуп. ценах
$fullComingRetail = 0.0; //Приход в розн.ценах
$fullComingWholesale = 0.0; //Приход в опт.ценах
$supplierDATA = array();
$cardSellingRetail = 0.0; // Продажи по безналичному расчёту в розн. ценах
$cardSellingWholesale = 0.0; // Продажи по безналичному расчёту в закуп. ценах
$cashSellingRetail = 0.0; // Продажи по наличному расчёту в розн. ценах
$cashSellingWholesale = 0.0; // Продажи по наличному расчёту в закуп. ценах
$invoiceBalanceRetail = 0.0; // Исходящий остаток в розн. ценах
$invoiceBalanceWholesale = 0.0; // Исходящий остаток в закуп. ценах

if (($handle = fopen($uploadfile, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1500, ";", $ENCLOSUREF)) !== FALSE) {
    $num = count($data);
    //Get report information

    if ($row === 0){
      $aptName = trim(explode(":", $data[0])[1]);
      if (trim($data[$OFFSET3]) !== $reportType) {
        echo "Проверьте отчёт";
        break;
      }
      $createTime = trim(explode("Напечатано:", $data[$OFFSET8])[1]);
      $dtime = DateTime::createFromFormat("d.m.Y, H:i:s", $createTime);
      $createTime = $dtime->format("Y-m-d H:i:s");
    }
    //Get report date
    if ($row === 5) {
      $reportStartDate = trim($data[$OFFSET9]);
      $reportEndDate = trim($data[$OFFSET11]);
    }

    //Get forward information
    if ($row === 10 ) {
      
      $forwardBalanceRetail = floatval(trim($data[$OFFSET4]));
      $forwardBalanceWholesale = floatval(trim($data[$OFFSET5]));
    }

    //Get coming Полный приход в аптеку
    if (isset($data[1]) && $data[1] === "Итого приход") {
      
      $fullComingRetail = floatval(trim($data[$OFFSET4]));
      $fullComingWholesale = floatval(trim($data[$OFFSET5]));
    }
    //Получаем приход в аптеку
    if (isset($data[3]) && !is_null($data[3]) && $data[3] === "" && substr($data[1], 0, 6) === "Дец") {
      if (trim(explode("Дец. закуп", $data[1])[1]) === "Медилон-Фармимэкс" OR trim(explode("Дец. закуп", $data[1])[1]) === "Протек" OR trim(explode("Дец. закуп", $data[1])[1]) === "Катрен") {
        $temp_array = array(explode("Дец. закуп ", $data[1])[1], floatval(trim($data[$OFFSET4])), floatval(trim($data[$OFFSET5])));
        $supplierDATA = array_merge($supplierDATA, $temp_array);
      }

    }
    //Получаем реализацию из аптеки
    if (isset($data[3]) && !is_null($data[3]) && $data[3] === "" && trim($data[1]) === "Реализация через ККМ покарточкам") {
      $cardSellingRetail = floatval(trim($data[$OFFSET4]));
      $cardSellingWholesale = floatval(trim($data[$OFFSET5]));
    }

    if ( isset($data[3]) &&!is_null($data[3]) && $data[3] === "" && trim($data[1]) === "Розничная реализация черезККМ") {
      $cashSellingRetail = floatval(trim($data[$OFFSET4]));
      $cashSellingWholesale = floatval(trim($data[$OFFSET5]));
    }
    // Исходящий остаток
    if (isset($data[3]) && !is_null($data[3]) && $data[3] === "" && substr($data[1], 0, 18) === "ИСХОДЯЩИЙ") {
      $invoiceBalanceRetail = floatval(trim($data[$OFFSET4]));
      $invoiceBalanceWholesale = floatval(trim($data[$OFFSET5]));
      break;
    }


    // echo "<p> $num полей в строке $row: <br /></p>\n";
    $row++;
    // // DEBUG
     // if ($row>5) {
     //  for ($c=0; $c < $num; $c++) {
     //    echo $c . ". ";
     //    echo $data[$c] . "<br />\n";
     //  }
     // }
  }
  fclose($handle);
}

echo "<br>Номер Аптеки: ".$aptName;
echo "<br>Дата формирования отчёта: ".$createTime;
echo "<br>Период отчёта с: ".$reportStartDate;
echo "<br>Период отчёта по: ".$reportEndDate;
echo "<br>Входящий остаток розн.: ".$forwardBalanceRetail;
echo "<br>Входящий остаток опт.: ".$forwardBalanceWholesale;
echo "<br>Итого приход розн.: ".$fullComingRetail;
echo "<br>Итого приход опт.: ".$fullComingWholesale;
echo "<br>Реализация через ККМ покарточкам розн.: ".$cardSellingRetail;
echo "<br>Реализация через ККМ покарточкам опт.: ".$cardSellingWholesale;
echo "<br>Розничная реализация черезККМ розн.: ".$cashSellingRetail;
echo "<br>Розничная реализация черезККМ опт.: ".$cashSellingWholesale;
echo "<br>Исходящий остаток розн.: ".$invoiceBalanceRetail;
echo "<br>Исходящий остаток опт.: ".$invoiceBalanceWholesale;

//print_r ($supplierDATA);
echo "<br>";
for ($i = 0; $i < count($supplierDATA); $i+=3) {
  echo "<br>Поставщик: ".$supplierDATA[$i];
  echo "<br>Розница: ".$supplierDATA[$i+1];
  echo "<br>Закупка: ".$supplierDATA[$i+2]."<br>";
}

  $dbconn = pg_connect("host=10.2.0.5 dbname=ap25 user=postgres password=flatron")
      or die('Could not connect: ' . pg_last_error());
  //$query = $q;

  $query = "SELECT \"id\" FROM \"AptCatalog\" WHERE \"aptName\" = '$aptName';";
  //$query = "SELECT DISTINCT (\"AID\") FROM viruchka ORDER BY \"AID\"";
  $result = pg_query($query) or die('Query failed: ' . pg_last_error());
  
  //Get AptID
  $AptID = 0;
  while ($line = pg_fetch_array($result, null, PGSQL_NUM)) {
    $AptID = $line[0];
    // foreach ($line as $key => $value) {
    //   # code...
    //   echo ($key).". ";
    //   echo ($value)."<br>";
    // }
  }

  $query = "SELECT \"id\" FROM \"AptData\" WHERE \"aptID\" = '$AptID' AND \"reportEndDate\" = '$reportEndDate';";
  //$query = "SELECT DISTINCT (\"AID\") FROM viruchka ORDER BY \"AID\"";    
  $result = pg_query($query) or die('Query failed: ' . pg_last_error());

  if (pg_fetch_array($result, null, PGSQL_NUM)) {
    echo "Отчёт уже существует";
    echo "<script>";
        echo "if(confirm(\"Отчёт уже существует. Заменить?\")) { ";
        $query = "UPDATE \"AptData\"  SET (
    \"createTime\", 
    \"reportStartDate\", 
    \"reportEndDate\",
    \"forwardBalanceRetail\",
    \"forwardBalanceWholesale\",
    \"fullComingRetail\",
    \"fullComingWholesale\",
    \"cardSellingRetail\",
    \"cardSellingWholesale\",
    \"cashSellingRetail\",
    \"cashSellingWholesale\",
    \"invoiceBalanceRetail\",
    \"invoiceBalanceWholesale\") = (
    '$createTime',
    '$reportStartDate',
    '$reportEndDate',
    '$forwardBalanceRetail',
    '$forwardBalanceWholesale',
    '$fullComingRetail',
    '$fullComingWholesale',
    '$cardSellingRetail',
    '$cardSellingWholesale',
    '$cashSellingRetail',
    '$cashSellingWholesale',
    '$invoiceBalanceRetail',
    '$invoiceBalanceWholesale') 
    WHERE \"aptID\" = '$AptID' AND \"reportEndDate\" = '$reportEndDate';";

    $result = pg_query($query) or die('Query failed: ' . pg_last_error());

    for ($i = 0; $i < count($supplierDATA); $i+=3) {
      $name = $supplierDATA[$i];
      $retail = $supplierDATA[$i+1];
      $wholesale = $supplierDATA[$i+2];
      $query = "UPDATE \"SupplData\" SET (
      \"curDate\", 
      \"retail\",
      \"wholesale\") = (
      '$createTime',
      '$retail',
      '$wholesale') 
      WHERE \"aptId\" = '$AptID' AND \"reportDate\" = '$reportEndDate' AND \"supplName\" = '$name';";
      $result = pg_query($query) or die('Query failed: ' . pg_last_error());
    }
          echo "alert(\"Данные обновлены!\");";
        echo "}";
    echo "</script>";
  }
  else {
    echo "Загружаем отчёт";
    $query = "INSERT INTO \"AptData\" (
    \"createTime\", 
    \"reportStartDate\", 
    \"reportEndDate\",
    \"forwardBalanceRetail\",
    \"forwardBalanceWholesale\",
    \"fullComingRetail\",
    \"fullComingWholesale\",
    \"cardSellingRetail\",
    \"cardSellingWholesale\",
    \"cashSellingRetail\",
    \"cashSellingWholesale\",
    \"invoiceBalanceRetail\",
    \"invoiceBalanceWholesale\",
    \"aptID\") VALUES (
    '$createTime',
    '$reportStartDate',
    '$reportEndDate',
    '$forwardBalanceRetail',
    '$forwardBalanceWholesale',
    '$fullComingRetail',
    '$fullComingWholesale',
    '$cardSellingRetail',
    '$cardSellingWholesale',
    '$cashSellingRetail',
    '$cashSellingWholesale',
    '$invoiceBalanceRetail',
    '$invoiceBalanceWholesale',
    '$AptID');";

    $result = pg_query($query) or die('Query failed: ' . pg_last_error());

    for ($i = 0; $i < count($supplierDATA); $i+=3) {
      $name = $supplierDATA[$i];
      $retail = $supplierDATA[$i+1];
      $wholesale = $supplierDATA[$i+2];
      $query = "INSERT INTO \"SupplData\" (
      \"curDate\", 
      \"aptId\", 
      \"retail\",
      \"wholesale\",
      \"supplName\",
      \"reportDate\"
      ) VALUES (
      '$createTime',
      '$AptID',
      '$retail',
      '$wholesale',
      '$name',
      '$reportEndDate');";
      $result = pg_query($query) or die('Query failed: ' . pg_last_error());

    }
  }



  pg_free_result($result);
  // Closing connection
  pg_close($dbconn);

?>