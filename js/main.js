(function ($) {
  "use strict";
  var curDay = new Date();
  var myChart = null;
  var dataDate = null;
  $('#month').val(curDay.toISOString().slice(0,7));

  $('#inputData').submit(function(event) {
    // устанавливаем первое число месяца, чтобы корректно распарсить месяц в пхп
    dataDate = $('#month').val() + '-01';
    // console.log("dataDate: ", dataDate);
    $('tbody').empty();
    $('tfoot').empty();
    var sumMedilon = 0.0;
    var sumKatren = 0.0;
    var sumProtek = 0.0;
    var sumTotalZak = 0.0;
    var sumViruchka = 0.0;
    var sumOstatok = 0.0;
    var sumOstatokRozn = 0.0;
    var zak_vir = 0.0;
    event.preventDefault();
    $.ajax({
      url: "query.php",
      type: "POST",
      dataType: "json",
      data: {q: "getData",
        d1: dataDate
      },
      success: function(data) {
        // $.each(data, function(key, value) {
        //   $('.test').append(key + "=>" + value.test1 + "<br>").hide().show('slow');
        // });

        // $.each(data, function(idx, obj) {
        //   $.each(obj, function(keyV, valueV){
        //     $('.test').append(idx + ":" + keyV +"=>"+valueV + "<br>").hide().show('slow');
        //   });
        //   $('.test').append("<br><br>");
        // });

        // console.log(data);
        $.each(data, function(key, value) {
          $('#allData>Caption').empty();
          $('#allData>Caption').append("Итоги на " + value.Date);
          var totalZak = parseFloat(value.Medilon) + parseFloat(value.Katren) + parseFloat(value.Protek);
          var color = '<tr>';

          zak_vir = 0;
          $('test').append(value.Date);
          if (totalZak > parseFloat(value.Viruchka)) {
            color = '<tr class=\"red clickable-row\" href=\"'+ value.AptId +'\">';
          }
          else {
            color = '<tr class=\"green clickable-row\" href=\"'+ value.AptId +'\">';
          }
          sumMedilon += parseFloat(value.Medilon);
          sumKatren += parseFloat(value.Katren);
          sumProtek += parseFloat(value.Protek);
          sumTotalZak += totalZak;
          sumViruchka += parseFloat(value.Viruchka);
          sumOstatok += parseFloat(value.Ostatok);
          sumOstatokRozn += parseFloat(value.OstatokRozn);

          if (value.Viruchka != 0 ) {
            zak_vir = parseFloat(100 * ((totalZak/value.Viruchka)-1));
          }

          $('#allData>tbody').append(color + 
            // '<td>' + '<a id ="apt-info-'+ key +'" href="'+ value.AptId +'">' + value.AptName + '</a>' + '</td>' +
            '<td>' + value.AptName + '</td>' +
            '<td>' + parseFloat(value.Medilon).format(0, 3, ' ', '.') + '</td>'+
            '<td>' + parseFloat(value.Katren).format(0, 3, ' ', '.') + '</td>'+
            '<td>' + parseFloat(value.Protek).format(0, 3, ' ', '.') + '</td>'+
            '<td>' + totalZak.format(0, 3, ' ', '.') + '</td>' +
            '<td>' + value.Viruchka.format(0, 3, ' ', '.') + '</td>' +
            '<td>' + zak_vir.format(2, 3, ' ', '.') + '</td>' +
            '<td>' + parseFloat(value.Ostatok).format(0, 3, ' ', '.') + '</td>' + 
            '<td>' + parseFloat(value.OstatokRozn).format(0, 3, ' ', '.') + '</td></tr>').hide().show('fast');
        });
        $('#allData>tfoot').append(
          '<tr>'+
          '<td>' + "Итого:" + '</td>' +
          '<td>' + sumMedilon.format(0, 3, ' ', '.') + '</td>' +
          '<td>' + sumKatren.format(0, 3, ' ', '.') + '</td>' +
          '<td>' + sumProtek.format(0, 3, ' ', '.') + '</td>' +
          '<td>' + sumTotalZak.format(0, 3, ' ', '.') + '</td>' +
          '<td>' + sumViruchka.format(0, 3, ' ', '.') + '</td>' +
          '<td>' + parseFloat(100 * ((sumTotalZak/sumViruchka)-1)).format(2, 3, ' ', '.') + '</td>' +
          '<td>' + sumOstatok.format(0, 3, ' ', '.') + '</td>' +
          '<td>' + sumOstatokRozn.format(0, 3, ' ', '.') + '</td>' +
          '</tr>'
          ).hide().show('fast');
      }
    });
  });
  // $(document).on('click','#allData a, .clickable-row', function(e) {
  $(document).on('click','.clickable-row', function(e) {
    dataDate = $('#month').val() + '-01';
    // console.log(dataDate);
    // var teamId = e.toElement.id.replace($teamInfoPrefix, '');
    var aptId = $(this).attr('href');

    $('#curAptData tbody').empty();
    $('#curAptData caption').empty();
    var DateArr = [];
    var ViruchkaArr = [];
    var OstatokArr = []
    e.preventDefault();

    $.ajax({
      url: "query.php",
      type: "POST",
      dataType: "json",
      data: {
        q: "getDataCur",
        d1: aptId,
        d2: dataDate
      },
      success: function(data) {
        $('#curAptData caption').append('Сводная по аптеке<br>'+ e.target.parentElement.firstChild.innerText);
        var prevVir = 0;
        $.each(data, function(key, value) {
          var color = '<tr>'
          var timestampDate = new Date(Date.parse(value.Date)).getDay();
          if (timestampDate == 6 || timestampDate == 0) {
            color = '<tr class=\"holiday"\>';
          }
          prevVir = value.ViruchkaRet - prevVir;
          DateArr.push(value.Date);
          ViruchkaArr.push(value.Viruchka);
          OstatokArr.push(value.Ostatok);
          $('#curAptData>tbody').append(color +
            '<td>' + value.Date + '</td>' +
            '<td>' + value.Viruchka.format(0, 3, ' ', '.') + '</td>' +
            '<td>' + value.ViruchkaRet.format(0, 3, ' ', '.') + '</td>' +
            '<td>' + prevVir.format(0, 3, ' ', '.') + '</td>' +
            '<td>' + parseFloat(value.Ostatok).format(0, 3, ' ', '.') + '</td></tr>').hide().show('fast');
          prevVir = value.ViruchkaRet;
        });
        // $('.chartParent').innerHTML = '&nbsp;';
        // $('.chartParent').append('<canvas id="myChart" max-width="400" max-height="400"><canvas>');
        createChart(DateArr, ViruchkaArr, OstatokArr);
        // createChartD3();
      }
    });
  });

  $(document).ready(function(){
  });

} (jQuery));
    
// Функция была нужна для правильного получения даты.
// function formatDate(date) {
//   var options = {
//   year: 'numeric',
//   month: '2-digit',
//   day: '2-digit'
//   };

//   result = date.toLocaleString("ru", options);
// return result;
// }

/**
 * Number.prototype.format(n, x, s, c)
 * 
 * @param integer n: кол-во знаков после запяторй
 * @param integer x: длина целой части
 * @param mixed   s: разделитель разрядов
 * @param mixed   c: разделитель дробной части
 */
Number.prototype.format = function(n, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
        num = this.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};

function createChart(DateArr, ViruchkaArr, OstatokArr) {
  
  var ctx = $("#myChart").get(0).getContext("2d");
  var myChart = new Chart(ctx, {
      type: 'line',
      data: {
          labels: DateArr,
          datasets: [{
              label: 'Выручка',
              data: ViruchkaArr,
              borderWidth: 1,
              backgroundColor: "rgba(75,192,192,0.4)",
          },
          {
              label: 'Остаток',
              data: OstatokArr,
              borderWidth: 1,
              backgroundColor: "rgba(255,99,132,0.2)",
          }]
      },
      options: {

          scales: {
              yAxes: [{
                  ticks: {
                      beginAtZero:true
                  },
                  // scaleLabel: "<%= value + ' + two = ' + (Number(value) + 2)   %>" ,
              }],
              xAxes: [{
                type: 'time',
                time: {
                  unit: 'day'
                  }
              }]
          }
      }
  });
  myChart.destroy();
};
