window.onload = function(){
  
    var ctx = document.getElementById("typy_zamowien").getContext("2d");
    window.myDoughnut = new Chart(ctx).Doughnut(pieData, {
      responsive: true,
      tooltipTemplate: "<%= label %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });                       

}