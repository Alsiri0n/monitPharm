<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Закупка-Выручка</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <form id="inputData">
        <p>Выберите месяц</p>
        <input type="month" name="month" id="month">
        <input type="submit" value="Отобразить" id="button">
    </form>
    <table id="allData">
        <caption>Итоги</caption>
        <thead>
            <tr>
                <th>Аптека</th>
                <th>Медилон, руб.</th>
                <th>Катрен, руб.</th>
                <th>Протек, руб.</th>
                <th>Общая<br>закупка, руб.</th>
                <th>Выручка<br>опт, руб</th>
                <th>Закупка/<br>Выручка, %</th>
                <th>Остаток<br>опт, руб.</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
    <table id="curAptData">
        <caption></caption>
        <thead>
             <tr>
                <th>Дата</th>
                <th>Выручка опт, руб.</th>
                <th>Выручка розн., руб.</th>
                <th>Дн.выручка, руб.</th>
                <th>Остаток опт, руб.</th>
            </tr> 
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
    <div class="chartParent">
        <canvas id="myChart" max-width="400" max-height="400"></canvas>
    </div>
    <script src="js/jquery-2.2.4.min.js"></script>
    <script src="js/moment.js"></script>
    <script src="js/Chart.js"></script>
    <script src="js/d3.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>