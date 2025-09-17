$(document).ready(function() {
  $("#zamowieniaForm").validate({
  rules: {
      email: {required: true,email: true,remote: "ajax/sprawdz_czy_jest_mail_klient.php?user_id=<?php echo $zamowienie->klient['id']; ?>"},
      nick: {remote: "ajax/sprawdz_czy_jest_nick.php?user_id=<?php echo $zamowienie->klient['id']; ?>"},
      imie: {required: true},
      nazwisko: {required: true},
      ulica: {required: true},
      kod_pocztowy: {required: true},
      miasto: {required: true},
      nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#spredazForm").val() == "1" ) { wynik = false; } return wynik; }},
      nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#sprzedazForm").val() == "1" ) { wynik = false; } return wynik;}},
      rabat: {range: [-100, 100],number: true}
  },
  messages: {
      email: {required: "Pole jest wymagane.",email: "Wpisano niepoprawny adres e-mail.",remote: "Taki adres jest już używany."},
      nick: {remote: "Taki login jest już używany."}
    }
  });

  $('input.datepicker').Zebra_DatePicker({
      view: 'years',
      format: 'd-m-Y',
      inside: false,
      readonly_element: false
  });

});

function edytuj_pole(pole,typ) {
  //
  if ( typ == 'textarea' ) {
       height = '100';
     } else {
       height = 'auto';
  }
  //
  $(function() {
    $("#"+pole).editable("ajax/zapisz_info_klienta_do_zamowienia.php?id_zamowienia=<?php echo (int)$_GET['id_poz']; ?>", { 
      indicator   : '<img src="obrazki/_loader_small.gif">',
      width       : 'auto',
      height      : height,
      type        : typ,
      submit      : "zapisz",
      placeholder : '&nbsp;'
    });
    $(".EdytujPole").bind("click", function() {
      $(this).prev().trigger("click");
    });              
  });
}  
 
function edytuj_dod_pole(pole) {
  //
  $(function() {
    $("#fields_"+pole).editable("ajax/zapisz_info_dod_pola_do_zamowienia.php?id_zamowienia=<?php echo (int)$_GET['id_poz']; ?>&nr=" + pole, { 
      indicator   : '<img src="obrazki/_loader_small.gif">',
      width       : '400',
      height      : '50',
      type        : 'textarea',
      submit      : "zapisz",
      placeholder : '&nbsp;'
    });
    $(".EdytujDodPole").bind("click", function() {
      $(this).prev().trigger("click");
    });              
  });
}      
           
function edytuj_platnosc(par1,par2) {
  $(function() {
    $(".editSel"+par1).editable("ajax/zapisz_info_klienta_do_zamowienia.php?id_zamowienia=<?php echo (int)$_GET['id_poz']; ?>&typ="+par1+"", { 
      data      : unescape(par2),
      type      : "select",
      submit    : "zapisz",
      indicator : '<img src="obrazki/_loader_small.gif">',
      style     : 'display: inline',
    });
    $(".EdytujPlatnosc").bind("click", function() {
      $(this).prev().trigger("click");
    });
  });
}

function edytuj_wysylke(par1,par2) {
  $(function() {
    $(".editSel"+par1).editable("ajax/zapisz_info_klienta_do_zamowienia.php?id_zamowienia=<?php echo (int)$_GET['id_poz']; ?>&typ="+par1+"", { 
      data      : unescape(par2),
      type      : "select",
      submit    : "zapisz",
      indicator : '<img src="obrazki/_loader_small.gif">',
      style     : 'display: inline',
    });
    $(".EdytujWysylke").bind("click", function() {
      $(this).prev().trigger("click");
    });
  });
}

function edytuj_dokument(par1,par2) {
  $(function() {
    $(".editSel"+par1).editable("ajax/zapisz_info_klienta_do_zamowienia.php?id_zamowienia=<?php echo (int)$_GET['id_poz']; ?>&typ="+par1+"", { 
      data      : unescape(par2),
      type      : "select",
      submit    : "zapisz",
      indicator : '<img src="obrazki/_loader_small.gif">',
      style     : 'display: inline',
    });
    $(".EdytujDokument").bind("click", function() {
      $(this).prev().trigger("click");
    });
  });
}

function edytuj_opiekuna(par1,par2) {
  $(function() {
    $(".editSel"+par1).editable("ajax/zapisz_opiekuna_do_zamowienia.php?id_zamowienia=<?php echo (int)$_GET['id_poz']; ?>", { 
      data      : unescape(par2),
      type      : "select",
      submit    : "zapisz",
      indicator : '<img src="obrazki/_loader_small.gif">',
      style     : 'display: inline',
    });
    $(".EdytujOpiekuna").bind("click", function() {
      $(this).prev().trigger("click");
    });
  });
}

function edytuj_ilosc_pobran(par1,par2) {
  $(function() {
    $(".editSel"+par1).editable("ajax/zapisz_ilosc_pobran_do_zamowienia.php?id_zamowienia=<?php echo (int)$_GET['id_poz']; ?>", { 
      data      : unescape(par2),
      type      : "select",
      submit    : "zapisz",
      indicator : '<img src="obrazki/_loader_small.gif">',
      style     : 'display: inline',
    });
    $(".EdytujPobranieOnline").bind("click", function() {
      $(this).prev().trigger("click");
    });
  });
}  