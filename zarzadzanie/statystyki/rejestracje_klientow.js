window.onload = function(){

    var ctxKlienciIlosc = document.getElementById("klienci_30_dni").getContext("2d");
    window.myLineKlienciIlosc = new Chart(ctxKlienciIlosc).Line(lineChartDataKlienciIlosc, {
      responsive: true,
      datasetStrokeWidth : 3,
      pointDotRadius : 5,
      tooltipTemplate: "Zarejestrowanych klientów: <%= value %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });  
    
    var ctxKlienciIloscMiesiace = document.getElementById("klienci_miesiace").getContext("2d");
    window.myLineKlienciIloscMiesiace = new Chart(ctxKlienciIloscMiesiace).Line(lineChartDataKlienciIloscMiesiace, {
      responsive: true,
      datasetStrokeWidth : 3,
      pointDotRadius : 5,
      tooltipTemplate: "Zarejestrowanych klientów: <%= value %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });                        

}     