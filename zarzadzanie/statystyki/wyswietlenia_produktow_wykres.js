window.onload = function(){

    var ctxProdukty = document.getElementById("ogladane_produkty").getContext("2d");
    window.myLineProdukty = new Chart(ctxProdukty).Bar(lineChartDataProdukty, {
      responsive: true,
      barStrokeWidth : 1,
      showXAxisLabel : false,
      tooltipTemplate: "<%= label %> - wyświetleń: <%= value %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });                          

}     