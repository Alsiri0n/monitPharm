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
            color = '<tr class=\"red\">';
          }
          else {
            color = '<tr class=\"green\">';
          }
          sumMedilon += parseFloat(value.Medilon);
          sumKatren += parseFloat(value.Katren);
          sumProtek += parseFloat(value.Protek);
          sumTotalZak += totalZak;
          sumViruchka += parseFloat(value.Viruchka);
          sumOstatok += parseFloat(value.Ostatok);

          if (value.Viruchka != 0 ) {
            zak_vir = parseFloat(100 * ((totalZak/value.Viruchka)-1));
          }

          $('#allData>tbody').append(color + 
            '<td>' + '<a id ="apt-info-'+ key +'" href="'+ value.AptId +'">' + value.AptName + '</a>' + '</td>' +
            '<td>' + parseFloat(value.Medilon).format(0, 3, ' ', '.') + '</td>'+
            '<td>' + parseFloat(value.Katren).format(0, 3, ' ', '.') + '</td>'+
            '<td>' + parseFloat(value.Protek).format(0, 3, ' ', '.') + '</td>'+
            '<td>' + totalZak.format(0, 3, ' ', '.') + '</td>' +
            '<td>' + value.Viruchka.format(0, 3, ' ', '.') + '</td>' +
            '<td>' + zak_vir.format(2, 3, ' ', '.') + '</td>' +
            '<td>' + parseFloat(value.Ostatok).format(0, 3, ' ', '.') + '</td></tr>').hide().show('fast');
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
          '</tr>'
          ).hide().show('fast');
      }
    });
  });
  $(document).on('click','#allData a', function(e) {
    // var dataDate = curDay.setDate(curDay.getDate());
    // if (curDay.getMonth() + 1 != parseInt($('#month').val().slice(5))) {
    //   dataDate = new Date(curDay.getFullYear(), curDay.getMonth(), 0).getTime();
    // }
    dataDate = $('#month').val() + '-01';
    console.log(dataDate);
    // var teamId = e.toElement.id.replace($teamInfoPrefix, '');
    var aptId = $(this).attr('href');

    $('#curAptData tbody').empty();
    $('#curAptData caption').empty();
    var DateArr = [];
    var ViruchkaArr = [];
    var OstatokArr = []
    e.preventDefault();
    // console.log(e.toElement.innerHTML)
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
        console.log(data);
        $('#curAptData caption').append('Сводная по аптеке<br>'+ e.target.innerText);
        $.each(data, function(key, value) {
          DateArr.push(value.Date);
          ViruchkaArr.push(value.Viruchka);
          OstatokArr.push(value.Ostatok);
          $('#curAptData>tbody').append('<tr>' +
            '<td>' + value.Date + '</td>' +
            '<td>' + value.Viruchka.format(0, 3, ' ', '.') + '</td>' +
            '<td>' + parseFloat(value.Ostatok).format(0, 3, ' ', '.') + '</td></tr>').hide().show('fast');
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

