$(document).ready(function() {

  $("#addrow").click(function(){
    
    var id = $(".UsunPozycjeListy").length;

    $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="UsunKontener"><a class="UsunPozycjeListy TipChmurka" href="javascript:void(0)"><b>Skasuj</b><img style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" /></a></div></td><td class="Paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="" size="8" name="parcel[dlugosc][]" class="kropkaPusta  required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[szerokosc][]" class="kropkaPusta required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[wysokosc][]" class="kropkaPusta required" /></td><td class="Paczka"><input type="text" value="" size="8" name="parcel[waga][]" class="kropkaPusta required" /></td><td class="Paczka"><input type="checkbox" value="1" name="parcel[niestandard][]" id="niestandard_'+id+'" /><label class="OpisForPustyLabel" for="niestandard_'+id+'"></label></td></tr>');
    
    pokazChmurki();
    
    if ($(".UsunPozycjeListy").length > 1) $(".UsunPozycjeListy").show();
    
  });
  
  $('body').on('click', '.UsunPozycjeListy', function() {
    var row = $(this).parents('.item-row');
    $(this).parents('.item-row').remove();
    if ($(".UsunPozycjeListy").length < 2) $(".UsunPozycjeListy").hide();
  });
  
});

