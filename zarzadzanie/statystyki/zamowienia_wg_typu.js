window.onload = function(){
  
    var ctx = document.getElementById("typy_zamowien").getContext("2d");
    window.myDoughnut = new Chart(ctx).Doughnut(pieData, {
      responsive: true,
      tooltipTemplate: "<%= label %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });                          

    var ctxZamowieniaWartosc = document.getElementById("typy_zamowien_wartosc").getContext("2d");
    window.myLineZamowieniaWartosc = new Chart(ctxZamowieniaWartosc).Bar(lineChartDataZamowieniaWartosc, {
      responsive: true,
      barStrokeWidth : 2,
      multiTooltipTemplate: " <%= datasetLabel %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });

}