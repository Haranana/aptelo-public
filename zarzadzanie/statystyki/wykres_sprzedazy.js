window.onload = function(){

    var ctxPrognoza = document.getElementById("canvas_prognoza").getContext("2d");
    window.myLinePrognoza = new Chart(ctxPrognoza).Bar(lineChartDataPrognoza, {
      responsive: true,
      barStrokeWidth : 1,
      tooltipTemplate: "Szacowana sprzedaż około: <%= KwotaChart(value) %> <%= label %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });  
    
    var ctxPrognozaMiesiace = document.getElementById("canvas_prognoza_miesiace").getContext("2d");
    window.myLinePrognozaMiesiace = new Chart(ctxPrognozaMiesiace).Bar(lineChartDataPrognozaMiesiace, {
      responsive: true,
      barStrokeWidth : 1,
      tooltipTemplate: "Szacowana sprzedaż około: <%= KwotaChart(value) %> <%= label %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    }); 

}