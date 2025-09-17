/**
 * SliderMieszany
 * wersja 1.0
 * www.shopgold.pl
 
 * slider NIE jest dostepny na licencji MIT lub GNU - wszelkie prawa autorskie posiada Studio Komputerowe Kamelia-Net Jacek Krysiak 
 * zabrania sie uzywania poza oprogramowaniem sklepu shopGold
 
 * ---- konfiguracja modulu ----
 - kontener - nazwa klasy lub id elementu kontenera (.xx lub #xx) - element ktory musi miec stala szerokosc w zaleznosci od rozdzielczosci
 - podkontener - nazwa klasy lub elementu w ktorym znajduja sie elementy do wyswietlania - musi miec duza szerokosc
 - pozycje - nazwa klasy elementow w podkontenerze - elementy ktore beda animowane
 - przyciski - nazwa klasy lub elementu ktory bedzie zawieral <span> - przyciski do kolejnych podstron
 - animacja_rodzaj - sposob animacji modulu:
      * brak - brak animacji
      * przewijanie - przewijanie od prawej do lewej
      * przenikanie - efekt przenikania
      * pomniejszanie - efekt pomniejszania i przenikania
      * spadanie - spadanie elementow z gory
      * pionowe_prostokaty - prostokaty animowane pionowo
      * pionowe_prostokaty_przemiennie - prostokaty animowane pionowo przemiennie
      * poziome_prostokaty - prostokaty animowane poziomo
      * poziome_prostokaty_przemiennie - prostokaty animowane poziomo przemiennie
      * kwadraty_pomniejszanie - kwadraty pomniejszanie i zanikanie
      * kwadraty_zanikanie - kwadraty zanikanie
  - animacja - czy modul ma sie sam animowac (tak/nie)
  - czas - czas pomiedzy animacjami jezeli sam sie animuje (w milisekundach)
 
**/

(function($) {

    $.BanneryMieszane = function( opcje ) {
    
        // jezeli jest resize ekranu to zresetuje modul i ustawi na nowo parametry
        $(window).resize(function() {            
            //
            if ( opcje.animacja == 'tak' ) {
                 clearTimeout(PonowanaAnimacjaMieszana);        
            }
            //
            BanneryMieszanePrzelicz(opcje);                      
            //
            if ( opcje.animacja == 'tak' ) {
                 PonowanaAnimacjaMieszana = setTimeout(function(){ $.BanneryMieszaneAnimacja( opcje ) }, opcje.czas); 
            }
            //
        });          

        setTimeout( function(){ BanneryMieszanePrzelicz(opcje) }, 300);
    
        function BanneryMieszanePrzelicz( opcje ) {

            // ustawia margines podkontenera na 0 - przy przeskalowaniu przegladarki zawsze wroci do wartosci poczatkowej
            $( opcje.podkontener ).css({ marginLeft: 0 });

            // szerokosc animacji na podstawie szerokosci glownego kontenera - szerokosc bedzie zmienna w zaleznosci od rozdzielczosci
            opcje['szerokoscAnimacji'] = $( opcje.kontener ).outerWidth();

            // usuwa akcje dla elementow
            $( opcje.podkontener + ' ' + opcje.pozycje ).off();

            // ustawia wysokosc elementow na auto        
            $( opcje.podkontener + ' ' + opcje.pozycje ).css( 'height', 'auto' );        

            // maksymalna wysokosc elementu w kontenerze
            opcje['maxWysokosc'] = 0;

            // petla do ustalenia stalej szerokosc dla wszystkich elementow - szerokosc ustalona powyzej
            $( opcje.podkontener + ' ' + opcje.pozycje ).each(function() {
                //         
                // ustawia szerokosc elementu
                $(this).width( opcje.szerokoscAnimacji );
                //
                // ustalenie maksymalnej wysokosci obrazkow
                opcje['wysokoscElementu'] = parseInt( $( this ).outerHeight() );
                if ( opcje.wysokoscElementu > opcje.maxWysokosc ) opcje.maxWysokosc = opcje.wysokoscElementu;          
                //
            });

            if ( opcje.maxWysokosc > 30 ) {
            
                 // ustawia wysokosc elementow - wyrownanie
                 $( opcje.podkontener + ' ' + opcje.pozycje ).css( 'height', opcje.maxWysokosc );
            
                // tylko jezeli animacja spadanie - do spadania musi ustawic na stale wysokosc kontanera
                if ( opcje.animacja_rodzaj == 'spadanie' ) {
                    $( opcje.kontener ).css({ height: opcje.maxWysokosc });        
                }         
        
            } else {
            
                $('.ModulRwdUkryj').each(function() {
                    //
                    if ( $(this).find( opcje.kontener ).length ) {
                        //
                        if ( $(this).css('display') != 'none' ) {
                              window.location.reload();
                        }
                        //
                    }
                    //
                });
            
            }
            
            // czysci element z przyciskami - istotne przy skalowaniu obrazu gdzie ilosc przyciskow jest zmienna
            $( opcje.przyciski ).html('');
            
            // petla do tworzenia przyciskow do podstron
            for ( t = 0; t < $( opcje.podkontener + ' ' + opcje.pozycje ).length; t++ ) { 
                //
                // dodaje do elementu przyciskow <span> jako przycisk
                // jezeli jest pierwszy przycisk dodaje do niego klase .On (wlaczony)
                if ( t == 0 ) {
                    $( opcje.przyciski ).append('<span class="On">' + t + '</span>');
                  } else {
                    $( opcje.przyciski ).append('<span>' + t + '</span>');
                }
                //
            }
            //   

            // pierwszemu elementowi span nadaje przezroczystosc 0.7
            $( opcje.podkontener + ' ' + opcje.pozycje ).find('span:first').css({ opacity: 0.7 }); 
            
            // akcja klikniecia w przycisk <span> 
            $( opcje.przyciski + ' span' ).off('click').click(function() {
                //
                // sprawdza czy nie jest klikniety aktywny .On przycisk
                if ( parseInt($( opcje.przyciski ).find('.On').html()) != parseInt( $(this).html() ) ) {
                    //
                    $.BanneryMieszaneAnimacja( opcje, $(this).html() );
                    //
                }
                //
            });  
            
            // jezeli jest animacja
            if ( opcje.animacja == 'tak' ) {
        
                // jezeli jest hover zatrzymuje animacje
                $( opcje.podkontener + ' ' + opcje.pozycje ).hover(
                function() {
                    // usuwa czas animacji
                    clearTimeout(PonowanaAnimacjaMieszana);
                },
                function() {
                    //tworzy nowy obiekt animacji od zera
                    PonowanaAnimacjaMieszana = setTimeout(function(){ $.BanneryMieszaneAnimacja( opcje ) }, opcje.czas);  
                });         

            }
            
        }

        // jezeli jest animacja
        if ( opcje.animacja == 'tak' ) {

            // definiuje element tablicy z funkcja animacji
            PonowanaAnimacjaMieszana = setTimeout(function(){ $.BanneryMieszaneAnimacja( opcje ) }, opcje.czas);            
            
        }

    }
    
    $.BanneryMieszaneAnimacja = function( opcje, nrPrzycisku ) {
    
        // usuwa obrazki tymczasowe
        $( opcje.kontener + ' .tmpImage').remove();
    
        // usuwa animacje przed wywolaniem nastepnej
        if ( opcje.animacja == 'tak' ) {
            //
            clearTimeout(PonowanaAnimacjaMieszana);
            // 
        }    
        
        // pobiera html aktualnego przycisku (numer)
        opcje['Aktywny'] = $( opcje.przyciski ).find('.On').html();        
    
        // jezeli w funkcji nie ma zdefiniowanego przycisku to oznacza ze funkcja nie zostala wywolana przez click tylko automatem animacji
        if ( nrPrzycisku == undefined ) {
            //
            // zwieksza przeskok o 1
            var nrPrzycisku = parseInt( opcje['Aktywny'] ) + 1;
            //
            // jezeli przeskok jest wiekszy niz liczba przyciskow to zeruje ustawienie
            if ( nrPrzycisku > $( opcje.przyciski + ' span' ).length - 1 ) {
                 nrPrzycisku = 0;
            }
            //
        }    

        // usuwa z przyciskow klase .On
        $( opcje.przyciski + ' span' ).removeClass('On');
        //
        // dodaje do aktywnego przycisku klase .On
        $( opcje.przyciski + ' span' ).each(function() {
            //
            if ( $(this).html() == nrPrzycisku ) {
                $(this).addClass('On');
            }
            //
        });
        
        // ukrywa wszystkie napisy span
        $( opcje.podkontener + ' ' + opcje.pozycje ).find('span').css({ opacity: 0 });

        switch ( opcje.animacja_rodzaj ) {
            // brak animacji
            case 'brak':
                //
                $( opcje.podkontener ).css({ marginLeft: (parseInt( nrPrzycisku ) * opcje.szerokoscAnimacji) * -1 });
                //
                // wylacza napisy span
                $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );
                //                
                //
                break;
                //
            // animacja przewijanie
            case 'przewijanie':
                //
                $( opcje.podkontener ).stop().animate({ marginLeft: (parseInt( nrPrzycisku ) * opcje.szerokoscAnimacji) * -1 }, 700, "SzybkoOut", function() {
                    // wylacza napisy span
                    $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );
                    //  
                });
                //
                break;
                //
            // animacja przenikanie
            case 'przenikanie':
                //
                // tworzy tymczasowy obrazek zeby bylo lagodne przechodzenie pomiedzy obrazkami
                tmpImg = $( opcje.podkontener + ' ' + opcje.pozycje )[ parseInt( opcje.Aktywny ) ];
                $( opcje.kontener ).append('<div style="position:absolute;width:100%;z-index:-1" id="tmpImg' + opcje.id + '" class="tmpImage">' + $(tmpImg).html() + '</div>');
                delete tmpImg;
                //
                // ukrywa wszystkie elementy przez opacity
                $( opcje.podkontener + ' ' + opcje.pozycje ).css({ opacity: 0 });
                // przesuwa margines calego podkontenera zeby pokazac kolejne elementy
                $( opcje.podkontener ).css({ marginLeft: (parseInt( nrPrzycisku ) * opcje.szerokoscAnimacji) * -1 });
                // animuje widocznosc elementow i po animacji usuwa tymczasowy obrazek
                $( opcje.podkontener + ' ' + opcje.pozycje ).stop().animate({ opacity: 1 }, 500, function() { 
                    //
                    $( opcje.kontener + ' #tmpImg' + opcje.id).remove();
                    //
                    // wylacza napisy span
                    $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );
                    //                    
                });
                //                    
                break;
                //
            // animacja pomniejszanie
            case 'pomniejszanie':
                //
                // ukrywa (animuje) przyciski przy tej animacji
                $( opcje.przyciski ).stop().animate({ opacity:0, marginLeft: (opcje.szerokoscAnimacji / 2) * -1 }, 100);
                //
                // tworzy tymczasowy obrazek zeby bylo lagodne przechodzenie pomiedzy obrazkami - aktualny i poprzedni
                $( opcje.kontener + ' .tmpImage').remove();
                $( opcje.kontener + ' .orgImage').remove();
                //
                // tworzy tymczasowy aktualny obrazek i daje mu absolute
                tmpImg = $( opcje.podkontener + ' ' + opcje.pozycje )[ parseInt( opcje.Aktywny ) ];
                $( opcje.kontener ).append('<div style="position:absolute;width:100%;z-index:2" id="tmpImg" class="tmpImage">' + $(tmpImg).html() + '</div>');
                delete tmpImg;
                //
                // tworzy tymczasowy nowy obrazek i daje mu absolute
                orgImg = $( opcje.podkontener + ' ' + opcje.pozycje )[ nrPrzycisku ];
                $( opcje.kontener ).append('<div style="position:absolute;width:10px;height:10px;z-index:1" id="orgImg" class="orgImage">' + $(orgImg).html() + '</div>');
                $( opcje.kontener + ' #orgImg').css({ opacity:0, marginTop:(opcje.maxWysokosc/2) - 5, marginLeft:(opcje.szerokoscAnimacji/2) - 5 });
                delete orgImg;                
                //

                // ukrywa wszystkie elementy przez opacity
                $( opcje.podkontener + ' ' + opcje.pozycje ).css({ opacity: 0 });
                // przesuwa margines calego podkontenera zeby pokazac kolejne elementy
                $( opcje.podkontener ).css({ marginLeft: (parseInt( nrPrzycisku ) * opcje.szerokoscAnimacji) * -1 });                
                
                // animuje widocznosc elementow i po animacji usuwa tymczasowy obrazek
                
                // usuwa napis span z tymczasowego obrazka
                $( opcje.kontener + ' #tmpImg').find('span').remove();
                // zmniejsza tymczasowy obrazek (zanika)
                $( opcje.kontener + ' #tmpImg').stop().animate({ height:10, width:10, marginTop:"+=" + (opcje.maxWysokosc/2), marginLeft:"+=" + (opcje.szerokoscAnimacji/2), opacity:0 }, 700, "SzybkoOut", function() { 
                    $( opcje.kontener + ' .tmpImage').remove(); 
                });
                
                // animuje tymczasowy nowy obrazek
                $( opcje.kontener + ' #orgImg').stop().animate({ height:opcje.maxWysokosc, width:opcje.szerokoscAnimacji, marginTop:0, marginLeft:0, opacity:0.5 }, 700, "SzybkoOut", function() { 
                    $( opcje.podkontener + ' ' + opcje.pozycje ).stop().animate({ opacity: 1 }, 300, function() { 
                        $( opcje.kontener + ' .orgImage').remove(), 
                        $( opcje.przyciski ).stop().animate({ opacity:1, marginLeft:0 }, 100); 
                        //
                        // wylacza napisy span
                        $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );
                        //                        
                    }); 
                });
                //                    
                break;
                //                
            // animacja spadanie
            case 'spadanie':
                //
                // ukrywa wszystkie elementy w kontenerze
                $( opcje.podkontener + ' ' + opcje.pozycje ).hide();
                // marginesem przenosi podkontener nad kontener - zeby nie byl widoczny
                $( opcje.podkontener ).css({ marginTop: (opcje.maxWysokosc + 10) * -1 });
                // przesuwa margines calego podkontenera zeby pokazac kolejne elementy
                $( opcje.podkontener ).css({ marginLeft: (parseInt( nrPrzycisku ) * opcje.szerokoscAnimacji) * -1 });
                // wlacza widocznosc elementow
                $( opcje.podkontener + ' ' + opcje.pozycje ).show();
                // animuje podkontener do marginesu gornego = 0
                $( opcje.podkontener ).stop().animate({ marginTop: 0 }, 700, "SzybkoOut", function() {
                    //
                    // wylacza napisy span
                    $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );
                    //
                });
                //
                break;                       
                //
            // animacja prostokaty pionowe
            case 'pionowe_prostokaty':
            case 'pionowe_prostokaty_przemiennie':
                //
                // ukrywa (animuje) przyciski przy tej animacji
                //$( opcje.przyciski ).stop().animate({ opacity:0, marginLeft: (opcje.szerokoscAnimacji / 2) * -1 }, 100);
                //
                // tworzy tymczasowy obrazek zeby bylo lagodne przechodzenie pomiedzy obrazkami - aktualny i poprzedni
                $( opcje.kontener + ' .tmpImage').remove();
                //
                // tworzy tymczasowy aktualny obrazek i daje mu absolute
                tmpImg = $( opcje.podkontener + ' ' + opcje.pozycje )[ parseInt( opcje.Aktywny ) ];
                iloscKolumn = parseInt(opcje.szerokoscAnimacji / 80);
                szerokoscProstokat = (opcje.szerokoscAnimacji / iloscKolumn);
                //
                for ( r = 0; r < iloscKolumn; r++ ) {
                    //                    
                    $( opcje.kontener ).append('<div style="position:absolute;width:' + szerokoscProstokat + 'px;z-index:2;overflow:hidden" id="tmpImg_' + r + '" class="tmpImage"></div>');
                    //
                    $( opcje.kontener + ' #tmpImg_' + r).html( '<div style="width:' + opcje.szerokoscAnimacji + 'px;margin-left:' + ( (r * szerokoscProstokat) * -1 ) + 'px">' + $(tmpImg).html() + '</div>' );
                    $( opcje.kontener + ' #tmpImg_' + r).css({ marginLeft: r * szerokoscProstokat });
                    //
                    // usuwa span z tmp obrazka
                    $( opcje.kontener + ' #tmpImg_' + r).find('span').remove();                 
                    //
                }
                delete tmpImg;
                delete szerokoscProstokat;
                
                // ukrywa wszystkie elementy przez opacity
                $( opcje.podkontener + ' ' + opcje.pozycje ).css({ opacity: 0 });
                // przesuwa margines calego podkontenera zeby pokazac kolejne elementy
                $( opcje.podkontener ).css({ marginLeft: (parseInt( nrPrzycisku ) * opcje.szerokoscAnimacji) * -1 });                
                // pokazuje wszystkie elementy przez opacity
                $( opcje.podkontener + ' ' + opcje.pozycje ).css({ opacity: 1 });

                for ( r = iloscKolumn; r > -1; r-- ) {
                    //
                    if ( opcje.animacja_rodzaj == 'pionowe_prostokaty_przemiennie' ) {
                        if ( r%2 == 0 ) {
                           wsp = -1;
                          } else {
                           wsp = 1;
                        }
                      } else {
                        wsp = -1;
                    }
                    //                    
                    $( opcje.kontener + ' #tmpImg_' + r).stop().delay(r * (100 - r)).animate({ marginTop: (opcje.maxWysokosc / 2) * wsp, opacity:0 }, 400, function() { 
                        //
                        $( this ).remove();
                        // wlacza napisy span
                        $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );
                        //
                    });
                    delete wsp;
                    //
                }
                
                delete iloscKolumn;
                //                    
                break;
                //     
            // animacja prostokaty poziome
            case 'poziome_prostokaty':
            case 'poziome_prostokaty_przemiennie':
                //
                // ukrywa (animuje) przyciski przy tej animacji
                //$( opcje.przyciski ).stop().animate({ opacity:0, marginLeft: (opcje.szerokoscAnimacji / 2) * -1 }, 100);
                //
                // tworzy tymczasowy obrazek zeby bylo lagodne przechodzenie pomiedzy obrazkami - aktualny i poprzedni
                $( opcje.kontener + ' .tmpImage').remove();
                //
                // tworzy tymczasowy aktualny obrazek i daje mu absolute
                tmpImg = $( opcje.podkontener + ' ' + opcje.pozycje )[ parseInt( opcje.Aktywny ) ];
                iloscWierszy = parseInt(opcje.maxWysokosc / 60);
                wysokoscProstokat = (opcje.maxWysokosc / iloscWierszy);
                //
                for ( r = 0; r < iloscWierszy; r++ ) {
                    //                    
                    $( opcje.kontener ).append('<div style="position:absolute;width:' + opcje.szerokoscAnimacji + 'px;height:' + wysokoscProstokat + 'px;z-index:2;overflow:hidden" id="tmpImg_' + r + '" class="tmpImage"></div>');
                    //
                    $( opcje.kontener + ' #tmpImg_' + r).html( '<div style="height:' + opcje.maxWysokosc + 'px;margin-top:' + ( (r * wysokoscProstokat) * -1 ) + 'px">' + $(tmpImg).html() + '</div>' );
                    $( opcje.kontener + ' #tmpImg_' + r).css({ marginTop: r * wysokoscProstokat });
                    //
                    // usuwa span z tmp obrazka
                    $( opcje.kontener + ' #tmpImg_' + r).find('span').remove();                 
                    //
                }
                delete tmpImg;
                delete wysokoscProstokat;              
                
                // ukrywa wszystkie elementy przez opacity
                $( opcje.podkontener + ' ' + opcje.pozycje ).css({ opacity: 0 });
                // przesuwa margines calego podkontenera zeby pokazac kolejne elementy
                $( opcje.podkontener ).css({ marginLeft: (parseInt( nrPrzycisku ) * opcje.szerokoscAnimacji) * -1 });                
                // pokazuje wszystkie elementy przez opacity
                $( opcje.podkontener + ' ' + opcje.pozycje ).css({ opacity: 1 });

                for ( r = iloscWierszy; r > -1; r-- ) {
                    //
                    if ( opcje.animacja_rodzaj == 'poziome_prostokaty_przemiennie' ) {
                        if ( r%2 == 0 ) {
                           wsp = -1;
                          } else {
                           wsp = 1;
                        }
                      } else {
                        wsp = 1;
                    }
                    // 
                    $( opcje.kontener + ' #tmpImg_' + r).stop().delay(r * (100 - r)).animate({ marginLeft: (opcje.szerokoscAnimacji / 2) * wsp, opacity:0 }, 400, function() { 
                        //
                        $( this ).remove();
                        // wlacza napisy span
                        $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );
                        //
                    });
                    delete wsp;
                    //
                }

                delete iloscWierszy;
                //                    
                break;
                //   
            case 'kwadraty_pomniejszanie':
            case 'kwadraty_zanikanie':
                //
                // ukrywa (animuje) przyciski przy tej animacji
                //$( opcje.przyciski ).stop().animate({ opacity:0, marginLeft: (opcje.szerokoscAnimacji / 2) * -1 }, 100);
                //
                // tworzy tymczasowy obrazek zeby bylo lagodne przechodzenie pomiedzy obrazkami - aktualny i poprzedni
                $( opcje.kontener + ' .tmpImage').remove();
                //
                // tworzy tymczasowy aktualny obrazek i daje mu absolute
                tmpImg = $( opcje.podkontener + ' ' + opcje.pozycje )[ parseInt( opcje.Aktywny ) ];
                iloscWierszy = parseInt(opcje.maxWysokosc / 80);
                iloscKolumn = parseInt(opcje.szerokoscAnimacji / 120)
                //
                wysokoscKwadrat = (opcje.maxWysokosc / iloscWierszy);
                szerokoscKwadrat = (opcje.szerokoscAnimacji / iloscKolumn);
                //
                for ( r = 0; r < iloscWierszy; r++ ) {
                    //                    
                    for ( t = 0; t < iloscKolumn; t++ ) {
                        //
                        $( opcje.kontener ).append('<div style="position:absolute;width:' + szerokoscKwadrat + 'px;height:' + wysokoscKwadrat+ 'px;z-index:2;overflow:hidden" id="tmpImg_' + r + '_' + t + '" class="tmpImage"></div>');
                        //
                        $( opcje.kontener + ' #tmpImg_' + r + '_' + t).html( '<div style="width:' + opcje.szerokoscAnimacji + 'px;height:' + opcje.maxWysokosc + 'px;margin-left:' + ( (t * szerokoscKwadrat) * -1 ) + 'px;margin-top:' + ( (r * wysokoscKwadrat) * -1 ) + 'px">' + $(tmpImg).html() + '</div>' );
                        $( opcje.kontener + ' #tmpImg_' + r + '_' + t).css({ marginTop: r * wysokoscKwadrat, marginLeft: t * szerokoscKwadrat });
                        //
                        // usuwa span z tmp obrazka
                        $( opcje.kontener + ' #tmpImg_' + r + '_' + t).find('span').remove();                 
                        //
                    }
                    //
                }
                delete tmpImg;
                
                // ukrywa wszystkie elementy przez opacity
                $( opcje.podkontener + ' ' + opcje.pozycje ).css({ opacity: 0 });
                // przesuwa margines calego podkontenera zeby pokazac kolejne elementy
                $( opcje.podkontener ).css({ marginLeft: (parseInt( nrPrzycisku ) * opcje.szerokoscAnimacji) * -1 });                
                // pokazuje wszystkie elementy przez opacity
                $( opcje.podkontener + ' ' + opcje.pozycje ).css({ opacity: 1 });

                for ( r = 0; r < iloscWierszy; r++ ) {
                    //                    
                    for ( t = 0; t < iloscKolumn; t++ ) {
                        // 
                        // pomniejszanie do srodka - przy delay zmienna r = od gory w dol, t - od lewej do prawej
                        if ( opcje.animacja_rodzaj == 'kwadraty_pomniejszanie' ) {
                            //
                            $( opcje.kontener + ' #tmpImg_' + r + '_' + t).stop().delay(t * (100 - (t * 2))).animate({ width:5, height:5, marginTop:"+=" + (wysokoscKwadrat/2), marginLeft:"+=" + (szerokoscKwadrat/2), opacity:0 }, 400, function() {
                                //
                                $( this ).remove();
                                // wlacza napisy span
                                $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );                            
                                //
                            });
                            //
                        }
                        if ( opcje.animacja_rodzaj == 'kwadraty_zanikanie' ) {
                            //
                            $( opcje.kontener + ' #tmpImg_' + r + '_' + t).stop().delay( Math.floor((Math.random() * 400) + 20) ).animate({  opacity:0 }, 400, function() { 
                                //
                                $( this ).remove();
                                // wlacza napisy span
                                $.BanneryMieszaneAnimacjaTekst( opcje, nrPrzycisku );                            
                                //
                            });
                            //
                        }
                        //
                    }
                    //
                }

                delete iloscWierszy;
                delete iloscKolumn;
                //                    
                break;
                //                 
        }     

        // jezeli jest animacja tworzy element tablicy i ponownie uruchamia animacje po okreslonym czasie
        if ( opcje.animacja == 'tak' ) {

            PonowanaAnimacjaMieszana = setTimeout(function(){ $.BanneryMieszaneAnimacja( opcje ) }, opcje.czas); 
        
        }        

    };
    
    // funkcja animacji tekstow
    $.BanneryMieszaneAnimacjaTekst = function( opcje, nrPrzycisku ) {
        //
        // kontener obiektu
        tekstBanneru = $( opcje.podkontener + ' ' + opcje.pozycje )[ nrPrzycisku ];
        // szuka span i go pokazuje
        $(tekstBanneru).find('span').stop().animate({ opacity: 0.7 }, 200 );
        //
    };

})(jQuery);
