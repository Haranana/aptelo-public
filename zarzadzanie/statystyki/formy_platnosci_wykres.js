window.onload = function(){
  
    var ctx = document.getElementById("formy_platnosci").getContext("2d");
    window.myPie = new Chart(ctx).Pie(pieData, {
      responsive: true,
      tooltipTemplate: "<%= label %>",
      tooltipFillColor: "rgba(0,0,0,0.7)"
    });

};