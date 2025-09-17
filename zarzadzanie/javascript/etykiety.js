$(document).ready(function() {

  $('input').click(function(){
    $(this).select();
  });

  $("#addrow").click(function(){

    var wiersz = $("#licznik").val();
    var wiersz_nastepny = parseFloat(wiersz) + parseFloat(1);
    
    $(".item-row:last").after('<tr class="item-row FakturaProduktEtykiety"><td class="FakturaProdukt"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="FakturaProdukt"></td><td class="FakturaProdukt"><textarea cols="100" rows="4" name="wiersz['+wiersz+'][adresat]"></textarea></td></tr>');
    
    pokazChmurki(); 

    $('#licznik').val( wiersz_nastepny );
    if ($(".UsunPozycjeListy").length > 1) $(".UsunPozycjeListy").show();
    
  });
  
  $('body').on('click', '.UsunPozycjeListy', function() {
    var row = $(this).parents('.item-row');
    $(this).parents('.item-row').remove();
    if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
  });
  
});
