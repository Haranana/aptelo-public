window.onload = function(){
  
    var ctxKlienci = document.getElementById("canvas_klienci").getContext("2d");
    window.myLine = new Chart(ctxKlienci).Line(lineChartDataKlienci, {
      responsive: true,
      datasetStrokeWidth : 3,
      pointDotRadius : 5,
      tooltipTemplate: "<%= value %> klientów",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });  
    
    var ctxZamowieniaWartosc = document.getElementById("canvas_zamowienia_wartosc").getContext("2d");
    window.myLineZamowieniaWartosc = new Chart(ctxZamowieniaWartosc).Line(lineChartDataZamowieniaWartosc, {
      responsive: true,
      datasetStrokeWidth : 3,
      pointDotRadius : 5,
      tooltipTemplate: "Wartość zamówień: <%= KwotaChart(value) %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });
    
    var ctxZamowieniaIlosc = document.getElementById("canvas_zamowienia_ilosc").getContext("2d");
    window.myLineZamowieniaIlosc = new Chart(ctxZamowieniaIlosc).Bar(lineChartDataZamowieniaIlosc, {
      responsive: true,
      barStrokeWidth : 1,
      tooltipTemplate: "Ilość zamówień: <%= value %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });  
  
}