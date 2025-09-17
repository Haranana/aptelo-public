window.onload = function(){

    var ctxFrazy = document.getElementById("wyszukiwane_frazy").getContext("2d");
    window.myLineFrazy = new Chart(ctxFrazy).Bar(lineChartDataFrazy, {
      responsive: true,
      barStrokeWidth : 1,
      showXAxisLabel : false,
      tooltipTemplate: "<%= label %> - wyszukań: <%= value %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });                          

}    