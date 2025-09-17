window.onload = function(){

    var ctxProdukty = document.getElementById("kupowane_produkty").getContext("2d");
    window.myLineProdukty = new Chart(ctxProdukty).Bar(lineChartDataProdukty, {
      responsive: true,
      barStrokeWidth : 1,
      showXAxisLabel : false,
      tooltipTemplate: "<%= label %> - kupionych: <%= value %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });                          

}     