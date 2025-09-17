<?php
/*
2  - Paczka pocztowa                                    paczkaPocztowaType
5  - Przesyłka pobraniowa                               przesylkaPobraniowaType                    - WYCOFANE
6  - Przesyłka polecona krajowa                         przesylkaPoleconaKrajowaType
7  - Przesyłka listowa z zadeklarowana wartością        przesylkaListowaZadeklarowanaWartoscType
8  - Przesyłka na warunkach szczególnych                przesylkaNaWarunkachSzczegolnychType       - WYCOFANE
10 - POCZTEX                                            uslugaKurierskaType
11 - E-PRZESYŁKA                                        ePrzesylkaType
12 - Pocztex kurier 48 (przesyłka biznesowa)            przesylkaBiznesowaType
15 - Przesyłka firmowa nierejestrowana
13 - Przesyłka firmowa polecona                         przesylkaFirmowaPoleconaType
14 - Uługa paczkowa                                     uslugaPaczkowaType
16 - Pocztex 2.0                                        pocztex2021Type

20 - Przesyłka polecona zagraniczna                     przesylkaPoleconaZagranicznaType
22 - Zagraniczna paczka do Unii Europoejskiej           paczkaZagranicznaType
23 - Global Expres                                      globalExpresType

*/

chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'Elektroniczny Nadawca';
    $apiKurier = new ElektronicznyNadawca();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $JestBufor = false;
        //Sprawdzenie czy jest aktywny bufor - jak nie ma to utworzenie nowego

        try
        {
            $E = new getEnvelopeBuforList();
            $wynik = $apiKurier->getEnvelopeBuforList($E);
        }
        catch(SoapFault $soapFault)
        {
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y')));
            //break;
        }

        if ( is_object($wynik) ) {
            if ( is_object($wynik->bufor) ) {
                if ( $wynik->bufor->idBufor != '' ) {
                    $JestBufor = true;
                }
            } elseif ( is_array($wynik->bufor) && count((array)$wynik->bufor) > 0 ) {
                $JestBufor = true;
            } elseif ( $wynik->bufor != '' ) {
                $JestBufor = true;
            }
        }
        if ( $JestBufor == false ) {

            $tmp = new createEnvelopeBufor();

            $B1 = new buforType();

            $B1->urzadNadania = $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_URZAD_NADANIA'];
            $B1->dataNadania  = date('d-m-Y');
            $B1->active       = true;
            $B1->opis         = 'Sklep-'.date('Y-m-d');

            $tmp->bufor = $B1;

            $wynikB = $apiKurier->createEnvelopeBufor($tmp);
        }

        unset($E, $wynik, $wynikB);

        //

        $tmp = new addShipment();

        //dane adresowe - wspolne dla wszystkich wysylek
        $A = new adresType();

        $A->nazwa       = $_POST['wysylka']['nazwa'];
        $A->nazwa2      = $_POST['wysylka']['nazwa1'];
        $A->ulica       = $_POST['wysylka']['ulica'];
        $A->numerDomu   = $_POST['wysylka']['numerDomu'];
        $A->numerLokalu = $_POST['wysylka']['numerLokalu'];
        $A->miejscowosc = $_POST['wysylka']['miejscowosc'];
        $A->kodPocztowy = $_POST['wysylka']['kod'];
        $A->kraj        = ( isset($_POST['kraj']) ? $_POST['kraj'] : 'Polska');
        $A->telefon     = ( isset($_POST['wysylka']['telefon']) ? $_POST['wysylka']['telefon'] : '');
        $A->email       = ( isset($_POST['wysylka']['email']) ? $_POST['wysylka']['email'] : '');
        $A->mobile      = ( isset($_POST['wysylka']['mobile']) ? str_replace('-', '', (string)$_POST['wysylka']['mobile']) : '');


        if ( $_POST["typ_wysylki"] == '2' ) {
            $P = new paczkaPocztowaType();

            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->zwrotDoslanie               = ( isset($_POST['ZwrotDoslanie']) ? true : false );
            $P->egzemplarzBiblioteczny      = false;
            $P->dlaOciemnialych             = false;
        }

        if ( $_POST["typ_wysylki"] == '5' ) {
            $P = new przesylkaPobraniowaType();
            $Y = new pobranieType();

            $P->posteRestante               = '';
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            $P->wartosc                     = ( isset($_POST['wartosc']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->masa                        = $_POST['masa'];
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );

            $Y->sposobPobrania              = $_POST['sposobPobrania'];
            $Y->kwotaPobrania               = round(($_POST['kwotaPobrania'] * 100), 0);
            $Y->nrb                         = $_POST['nrb'];
            $Y->tytulem                     = $_POST['tytulem'];

            $P->pobranie = $Y;
        }

        if ( $_POST["typ_wysylki"] == '6' ) {
            $P = new przesylkaPoleconaKrajowaType();

            $P->epo                         = '';
            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->format                      = $_POST['format'];
            $P->masa                        = $_POST['masa'];
            $P->egzemplarzBiblioteczny      = false;
            $P->dlaOciemnialych             = false;
        }

        if ( $_POST["typ_wysylki"] == '7' ) {
            $P = new przesylkaListowaZadeklarowanaWartoscType();

            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->zwrotDoslanie               = ( isset($_POST['ZwrotDoslanie']) ? true : false );
        }

        if ( $_POST["typ_wysylki"] == '8' ) {
            $P = new przesylkaNaWarunkachSzczegolnychType();

            $P->posteRestante               = '';
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->wartosc                     = ( isset($_POST['wartosc']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->masa                        = $_POST['masa'];
        }

        if ( $_POST["typ_wysylki"] == '10' ) {
            $P = new uslugaKurierskaType();
            $Y = new pobranieType();
            //$D = new doreczenieUslugaKurierskaType();
            //$O = new odbiorPrzesylkiOdNadawcyType();
            //$ZD = new zwrotDokumentowKurierskaType();
            $PO = new potwierdzenieOdbioruKurierskaType();
            $PD = new potwierdzenieDoreczeniaType();
            $U = new ubezpieczenieType();
            $OP = new opakowanieKurierskaType();
            $E = new urzadWydaniaEPrzesylkiType();


            $P->posteRestante               = '';
            $P->termin                      = $_POST['terminRodzaj'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            $P->zawartosc                   = $_POST['zawartosc'];
            $P->ponadgabaryt                = ( isset($_POST['ponadgabaryt']) ? true : false );
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );
            $P->uiszczaOplate               = $_POST['uiszczaOplate'];

            if ( isset($_POST['kopertaFirmowa']) ) {
                $P->opakowanie = 'FIRMOWA_DO_1KG';
            }

            if ( isset($_POST['pobranie']) && $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = round(($_POST['kwotaPobrania']*100), 0);
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;

            //odbiorPrzesylkiOdNadawcyType Object
            //$O->wSobote                    = '';
            //$O->wNiedzieleLubSwieto        = '';
            //$O->wGodzinachOd20Do7          = '';

            //$P->odbiorPrzesylkiOdNadawcy = $O;

            //doreczenieUslugaKurierskaType Object
            //$D->oczekiwanyTerminDoreczenia = '';
            //$D->oczekiwanaGodzinaDoreczenia= '';
            //$D->wSobote                    = '';
            //$D->w90Minut                   = '';
            //$D->wNiedzieleLubSwieto        = '';
            //$D->doRakWlasnych              = '';
            //$D->wGodzinachOd20Do7          = '';
            //$D->po17                       = '';

            //$P->doreczenie = $D;

            //zwrotDokumentowKurierskaType Object
            //$ZD->rodzajPocztex             = '';
            //$ZD->rodzajPaczka              = '';
            //$ZD->rodzajList                = '';

            //$P->zwrotDokumentow = $ZD;

            //potwierdzenieOdbioruKurierskaType Object
            if ( isset($_POST['PotwierdzenieOdbioru']) ) {
                $PO->ilosc                     = $_POST['iloscPotwierdzenOdbioru'];
                $PO->sposob                    = $_POST['RodzajPotwierdzenOdbioru'];
            } else {
                $PO->ilosc                     = '';
                $PO->sposob                    = '';
            }

            $P->potwierdzenieOdbioru = $PO;

            //potwierdzenieDoreczeniaType Object
            if ( isset($_POST['PotwierdzenieDoreczenia']) ) {
                $PD->sposob                    = $_POST['RodzajPotwierdzenDoreczenia'];
                $PD->kontakt                   = $_POST['danePotwierdzenDoreczenia'];
            } else {
                $PD->sposob                    = '';
                $PD->kontakt                   = '';
            }

            $P->potwierdzenieDoreczenia = $PD;

            //ubezpieczenieType Object
            if ( isset($_POST['CzyUbezpieczenie']) ) {
                $U->rodzaj                     = 'STANDARD';
                $U->kwota                      = round(($_POST['ubezpieczenie_wart']*100), 0);

                $P->ubezpieczenie = $U;
            }

            // Ustawienie punktu odbioru przesylki
            if ( isset($_POST['OdbiorWPunkcie']) && ( isset($_POST['urzad_wydajacy_eprzesylke_pni']) && $_POST['urzad_wydajacy_eprzesylke_pni'] != '' ) ) {
                $E->id = $_POST['urzad_wydajacy_eprzesylke_pni'];
                $P->urzadWydaniaEPrzesylki = $E;
            }
            
        }

        if ( $_POST["typ_wysylki"] == '11' ) {
            $P = new ePrzesylkaType();
            $Y = new pobranieType();

            //$P->urzadWydaniaEPrzesylki        = $_POST['urzadWydaniaEPrzesylki'];
            $P->masa                          = $_POST['masa'];
            $P->eSposobPowiadomieniaAdresata  = $_POST['eSposobPowiadomieniaAdresata'];
            $P->eSposobPowiadomieniaNadawcy   = $_POST['eSposobPowiadomieniaNadawcy'];
            $P->eKontaktAdresata              = $_POST['eKontaktAdresata'];
            $P->eKontaktNadawcy               = $_POST['eKontaktNadawcy'];
            $P->ostroznie                     = ( isset($_POST['ostroznie']) ? true : false );
            $P->wartosc                       = ( isset($_POST['wartosc']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );

            if ( $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = round(($_POST['kwotaPobrania']*100), 0);
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;
        }

        if ( $_POST["typ_wysylki"] == '12' ) {
            $P = new przesylkaBiznesowaType();
            $Y = new pobranieType();
            $U = new ubezpieczenieType();
            $E = new urzadWydaniaEPrzesylkiType();

            $P->masa                        = $_POST['masa'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            if ( $_POST['opis'] != '' ) {
                $P->opis                   = $_POST['opis'];
            }

            //$P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );

            if ( isset($_POST['pobranie']) && $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = round(($_POST['kwotaPobrania']*100), 0);
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;

            // Ustawienie punktu odbioru przesylki
            if ( isset($_POST['OdbiorWPunkcie']) && ( isset($_POST['urzad_wydajacy_eprzesylke_pni']) && $_POST['urzad_wydajacy_eprzesylke_pni'] != '' ) ) {
                $E->id = $_POST['urzad_wydajacy_eprzesylke_pni'];
                $P->urzadWydaniaEPrzesylki = $E;
            }
            
            //ubezpieczenieType Object
            if ( isset($_POST['CzyUbezpieczenie']) ) {
                $U->rodzaj                     = 'STANDARD';
                $U->kwota                      = round(($_POST['ubezpieczenie_wart']*100), 0);

                $P->ubezpieczenie = $U;
            }
        }

        if ( $_POST["typ_wysylki"] == '13' ) {
            $P = new przesylkaFirmowaPoleconaType();

            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->gabaryt                     = $_POST['gabaryt'];
            //$P->miejscowa                   = $_POST['miejscowa'];
            $P->kategoria                   = $_POST['kategoria'];
            $P->masa                        = $_POST['masa'];
            $P->egzemplarzBiblioteczny      = false;
            $P->dlaOciemnialych             = false;
        }

        if ( $_POST["typ_wysylki"] == '14' ) {
            $P = new uslugaPaczkowaType();
            $Y = new pobranieType();
            //$D = new doreczenieUslugaPocztowaType();
            //$ZD = new zwrotDokumentowPaczkowaType();
            $PO = new potwierdzenieOdbioruPaczkowaType();
            $PD = new potwierdzenieDoreczeniaType();
            $U = new ubezpieczenieType();
            $E = new urzadWydaniaEPrzesylkiType();

            $P->termin                      = $_POST['terminRodzaj'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            $P->zawartosc                   = $_POST['zawartosc'];
            $P->ponadgabaryt                = ( isset($_POST['ponadgabaryt']) ? true : false );
            $P->uiszczaOplate               = $_POST['uiszczaOplate'];
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );

            if ( isset($_POST['pobranie']) && $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = round(($_POST['kwotaPobrania']*100), 0);
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;

            //doreczenieUslugaPocztowaType Object
            //$D->oczekiwanyTerminDoreczenia = '';
            //$D->oczekiwanaGodzinaDoreczenia= '';
            //$D->wSobote                    = '';
            //$D->doRakWlasnych              = '';

            //$P->doreczenie = $D;

            //zwrotDokumentowPaczkowaType Object
            //$P->zwrotDokumentow            = '';

            //potwierdzenieOdbioruPaczkowaType Object
            if ( isset($_POST['PotwierdzenieOdbioru']) ) {
                $PO->ilosc                     = $_POST['iloscPotwierdzenOdbioru'];
                $PO->sposob                    = $_POST['RodzajPotwierdzenOdbioru'];
            } else {
                $PO->ilosc                     = '';
                $PO->sposob                    = '';
            }

            $P->potwierdzenieOdbioru = $PO;

            //potwierdzenieDoreczeniaType Object
            if ( isset($_POST['PotwierdzenieDoreczenia']) ) {
                $PD->sposob                    = $_POST['RodzajPotwierdzenDoreczenia'];
                $PD->kontakt                   = $_POST['danePotwierdzenDoreczenia'];
            } else {
                $PD->sposob                    = '';
                $PD->kontakt                   = '';
            }

            $P->potwierdzenieDoreczenia = $PD;

            //ubezpieczenieType Object
            if ( isset($_POST['CzyUbezpieczenie']) ) {
                $U->rodzaj                     = 'STANDARD';
                $U->kwota                      = round(($_POST['ubezpieczenie_wart']*100), 0);

                $P->ubezpieczenie = $U;
            }

            if ( isset($_POST['OpakowanieRodzaj']) ) {
                $P->opakowanie                 = $_POST['RodzajOpakowania'];
            }

            // Ustawienie punktu odbioru przesylki
            if ( isset($_POST['OdbiorWPunkcie']) && ( isset($_POST['urzad_wydajacy_eprzesylke_pni']) && $_POST['urzad_wydajacy_eprzesylke_pni'] != '' ) ) {
                $E->id = $_POST['urzad_wydajacy_eprzesylke_pni'];
                $P->urzadWydaniaEPrzesylki = $E;
            }
        }

        if ( $_POST["typ_wysylki"] == '16' ) {

            if ( $_POST['serwis'] == 'Q' ) {
                $P = new pocztex2021KurierType();
            } elseif ( $_POST['serwis'] == 'D' ) {
                $P = new pocztex2021NaDzisType();
                $_POST['kanal_nadania'] = '3';
            }
            $Y = new pobranieType();
            $U = new ubezpieczenieType();
            $Z = new zawartoscPocztex2021Type();

            // Ustawienie punktu nadania przesylki
            if ( $_POST['kanal_nadania'] == '1' ) {
                $N = new punktNadaniaType();
                $N->id = $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_URZAD_NADANIA'];
                $P->punktNadania = $N;
            }

            // Ustawienie punktu odbioru przesylki
            if ( $_POST['serwis'] == 'Q' ) {
                if ( isset($_POST['OdbiorWPunkcie']) && ( isset($_POST['urzad_wydajacy_eprzesylke_pni']) && $_POST['urzad_wydajacy_eprzesylke_pni'] != '' ) ) {
                    $E = new punktOdbioruType();
                    $E->id = $_POST['urzad_wydajacy_eprzesylke_pni'];
                    $P->punktOdbioru = $E;
                }
            }

            if ( $_POST['serwis'] == 'D' ) {
                if ( $_POST['obszar'] == 'M' ) {
                    $P->odleglosc = $_POST['odleglosc'];
                }
                $P->obszar = ( $_POST['obszar'] == 'M' ? 'MIASTO' : 'KRAJ' );
            }

            if ( $_POST['serwis'] == 'D' && $_POST['obszar'] == 'K' ) {
                $_POST['gabaryt'] = '';
            }

            $P->masa                        = $_POST['masa'];
            $P->format                      = $_POST['gabaryt'];

            $P->kopertaPocztex           = ( isset($_POST['KopertaFirmowa']) ? true : false );

            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? round(($_POST['wartosc']*100), 0) : '' );
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            $P->ponadgabaryt                = ( isset($_POST['ponadgabaryt']) ? true : false );
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );
            $P->odbiorWSobote               = ( isset($_POST['OdbiorWSobote']) ? true : false );


            if ( $_POST['opis'] != '' ) {
                $Z->zawartoscInna           = $_POST['opis'];
            }

            $P->zawartosc = $Z;

            //pobranie
            if ( isset($_POST['pobranie']) && $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = round(($_POST['kwotaPobrania']*100), 0);
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;

            //ubezpieczenie
            if ( isset($_POST['CzyUbezpieczenie']) ) {
                $U->rodzaj                     = 'STANDARD';
                $U->kwota                      = round(($_POST['ubezpieczenie_wart']*100), 0);
                $P->ubezpieczenie = $U;
            }

        }



















        if ( $_POST["typ_wysylki"] == '20' ) {
            $P = new przesylkaPoleconaZagranicznaType();

            $P->posteRestante               = false;
            $P->masa                        = $_POST['masa'];
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '0');
        }

        if ( $_POST["typ_wysylki"] == '22' ) {
            $P = new paczkaZagranicznaType();
            $Z = new zwrotType();

            $P->posteRestante               = '';
            $P->kategoria                   = $_POST['kategoria'];
            $P->masa                        = $_POST['masa'];
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '0');
            $P->ekspres                     = '';
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? round(($_POST['wartosc']*100), 0) : '' );


            if ( isset($_POST['zwrot_natychmiast']) || isset($_POST['zwrot_po_liczbie_dni']) ) {
                $Z->zwrotPoLiczbieDni           = '15';
            }
            if ( isset($_POST['porzucona']) ) {
                $Z->traktowacJakPorzucona       = true;
            } else {
                $Z->traktowacJakPorzucona       = false;
            }
            if ( !isset($_POST['porzucona']) ) {
                $Z->sposobZwrotu                = $_POST['sposob_zwr_zagr'];
            }

            $P->zwrot = $Z;
        }

        if ( $_POST["typ_wysylki"] == '23' ) {
            $P = new globalExpresType();

            $P->posteRestante               = '';
            $P->kategoria                   = $_POST['kategoria'];
            $P->masa                        = $_POST['masa'];
            $P->zawartosc                   = $_POST['zawartosc'];

            //ubezpieczenieType Object
            if ( isset($_POST['CzyUbezpieczenie']) ) {
                $U->rodzaj                     = 'STANDARD';
                $U->kwota                      = round(($_POST['ubezpieczenie_wart']*100), 0);

                $P->ubezpieczenie = $U;
            }

            //potwierdzenieDoreczeniaType Object
            if ( isset($_POST['PotwierdzenieDoreczenia']) ) {
                $PD->sposob                    = $_POST['RodzajPotwierdzenDoreczenia'];
                $PD->kontakt                   = $_POST['danePotwierdzenDoreczenia'];
            } else {
                $PD->sposob                    = '';
                $PD->kontakt                   = '';
            }

            $P->potwierdzenieDoreczenia = $PD;
        }


        $P->guid = Funkcje::Guid();// wygenerowany guid

        $P->adres = $A;

        $tmp->przesylki[] = $P;

//        echo '<pre>';
//        echo print_r($tmp);
//        echo '</pre>';


        $przesylka = $apiKurier->addShipment($tmp); // wysłanie zapytania

//        echo '<pre>';
//        echo print_r($przesylka);
//        echo '</pre>';

        if ( is_object($przesylka) ) {

            $komunikat = '';
            if ( isset($przesylka->retval->error) && is_array($przesylka->retval->error) ) {
                foreach ( $przesylka->retval->error as $error ) {
                    $komunikat .= $error->errorNumber . ': ' . str_replace('"', '', (string)$error->errorDesc) . '<br />';
                }
            } elseif ( isset($przesylka->retval->error) && !is_array($przesylka->retval->error) ) {
                $komunikat .= $przesylka->retval->error->errorNumber . ': ' . str_replace('"', '', (string)$przesylka->retval->error->errorDesc) . '<br />';
            } else {
                $pola = array(
                        array('orders_id',$filtr->process($_POST["id"])),
                        array('orders_shipping_type',$api),
                        array('orders_shipping_number',( isset($przesylka->retval->numerNadania) ? $przesylka->retval->numerNadania : 'BRAK')),
                        array('orders_shipping_weight',$_POST['masa']/1000),
                        array('orders_parcels_quantity','1'),
                        array('orders_shipping_status','0'),
                        array('orders_shipping_date_created', 'now()'),
                        array('orders_shipping_date_modified', 'now()'),
                        array('orders_shipping_comments', ( isset($przesylka->retval->guid) ? $przesylka->retval->guid : 'BRAK' )),
                );

                $db->insert_query('orders_shipping' , $pola);
                unset($pola);
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
            }
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    $zapytanie = "SELECT * FROM orders_shipping WHERE orders_shipping_type = 'Elektroniczny Nadawca' AND orders_shipping_status = '0' AND DATE(orders_shipping_date_created) < CURRENT_DATE";
    $sql = $db->open_query($zapytanie);

    if ( $db->ile_rekordow($sql) > 0 ) {
        echo Okienka::pokazOkno('Błąd', 'W buforze są niewysłane paczki z wcześniejszych dni<br />najpierw należy opróżnić bufor', 'index.php'); 
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

    if ( isset($komunikat) && $komunikat != '' ) {
        echo Okienka::pokazOkno('Błąd', $komunikat);
    }

    $haslo = new getPasswordExpiredDate();
    $DataHasla = $apiKurier->getPasswordExpiredDate($haslo);

    if ( isset($DataHasla->dataWygasniecia) ) {
        $dataBiezaca = time();
        $ostrzezenie = '';
        if ( $dataBiezaca > FunkcjeWlasnePHP::my_strtotime($DataHasla->dataWygasniecia) ) {
            $ostrzezenie = $DataHasla->dataWygasniecia;
        }
    } else {
        $ostrzezenie = '<span style="color:red;">hasło wygasło</span>';
    }
    ?>

    <div id="naglowek_cont">Tworzenie wysyłki - <?php echo 'data ważności hasła w serwisie e-nadawca : ' . $ostrzezenie; ?></div>
    <div id="cont">
    
        <?php
        if ( !isset($_GET['id_poz']) ) {
             $_GET['id_poz'] = 0;
        }     
        if ( !isset($_GET['zakladka']) ) {
             $_GET['zakladka'] = '0';
        }      
        
        if ( (int)$_GET['id_poz'] == 0 ) {
        ?>

            <div class="poleForm"><div class="naglowek">Wysyłka</div>
                <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
            </div>    
      
            <?php
        } else {
        ?>

        <div class="poleForm">
            <div class="naglowek">Wysyłka za pośrednictwem <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

            <div class="pozycja_edytowana">  

                <?php
                //if ( $apiKurier->success ) {
                    $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
                    $waga_produktow = $zamowienie->waga_produktow * 1000;
                    $wymiary        = array();

                    $AdresOK = true;
                    $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
                    $adres_dom_lokal = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);

                    $PrzeksztalconyAdres = implode(' ', $adres_klienta); 
                    if ( $PrzeksztalconyAdres != $zamowienie->dostawa['ulica'] ) {
                        $AdresOK = false;
                    }

                    ?>

                    <script src="https://mapa.ecommerce.poczta-polska.pl/widget/scripts/ppwidget.js"></script>

                    <script>
                    $(document).ready(function() {
                      <?php
                      if ( !$AdresOK ) {
                        ?>
                        $( "<p style='padding:10px 25px 5px 25px;'><span class='ostrzezenie'>Sprawdź adres odbiorcy</span></p><p style='padding:0 20px 5px 25px;'><span><?php echo addslashes($zamowienie->dostawa['ulica']); ?></span></p>" ).insertBefore("#AdresOdbiorcy");
                        <?php
                      }
                      ?>
                    });
                    </script>

                    <script charset="utf-8">
                    $(function() {
                      var a = <?php echo ( isset($_POST['typ_wysylki']) ? $_POST['typ_wysylki'] : $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_PRZESYLKA_DOMYSLNA']); ?>;
                      var b = <?php echo ( isset($_POST['wysylka']['masa']) ? $_POST['wysylka']['masa'] : $waga_produktow ); ?>;
                      var c = '<?php echo ( isset($_POST['wysylka']['panstwo']) ? $_POST['wysylka']['panstwo'] : $zamowienie->dostawa['kraj'] ); ?>';
                      var d = '<?php echo addslashes((string)$adres_klienta['ulica']) .','. $zamowienie->dostawa['miasto']; ?>';
                      var e = '<?php echo $_GET['id_poz']; ?>';

                      $("#formularz").load("ajax/enadawca_formularz.php", {valueType: a, wagaProduktow: b, krajDostawy: c, miastoDostawy: d, IdZam: e, html: encodeURIComponent($("#addhtml").html())});

                      $('#typ_wysylki').bind('change', function(ev) {
                         var value = $(this).val();
                         var waga  = <?php echo $waga_produktow; ?>;
                         var panstwo  = '<?php echo $zamowienie->dostawa['kraj']; ?>';
                         var miasto  = '<?php echo addslashes((string)$adres_klienta['ulica']) .','. $zamowienie->dostawa['miasto']; ?>';
                         var idzam  = '<?php echo $_GET['id_poz']; ?>';
                         $("#formularz").empty();
                         $("#formularz").html('<div style="margin:10px;margin-top:20px;text-align:center;"><img src="obrazki/_loader.gif"></div>');
                         $.ajax({
                            type: "POST",
                            url:  "ajax/enadawca_formularz.php",
                            data: {valueType: value, wagaProduktow: waga, krajDostawy: panstwo, miastoDostawy: miasto, IdZam: idzam, html: encodeURIComponent($("#addhtml").html())},
                            success: function(msg){
                                    $("#formularz").html(msg).show(); 
                                    $(".kropka").change(		
                                      function () {
                                        var type = this.type;
                                        var tag = this.tagName.toLowerCase();
                                        if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                                            //
                                            zamien_krp($(this),'0.00');
                                            //
                                        }
                                      }
                                    ); 
                            },
                         });
                      });
                    });
                    </script>

                    <form action="sprzedaz/zamowienia_wysylka_enadawca.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
                        <div>
                            <input type="hidden" name="akcja" value="zapisz" />
                            <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                            <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                            <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                            <input type="hidden" id="wartosc_ubezpieczenia_val" name="wartosc_ubezpieczenia_val" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_KWOTA_UBEZPIECZENIA']; ?>" />
                        </div>

                        <div class="TabelaWysylek">

                            <div class="OknoPrzesylki">

                                <div class="poleForm">

                                    <div class="naglowek">Informacje o przesyłce</div>

                                    <p>
                                        <label class="required" for="typ_wysylki">Usługa:</label>
                                        <?php
                                        $domyslnie = $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_PRZESYLKA_DOMYSLNA'];
                                        if ( isset($_POST['typ_wysylki']) ) {
                                            $domyslnie = $_POST['typ_wysylki'];
                                        }
                                        $tablica = array(
                                                       array('id' => '2', 'text' => 'Paczka pocztowa'),
                                                       array('id' => '6', 'text' => 'Przesyłka polecona'),
                                                       array('id' => '7', 'text' => 'Przesyłka listowa z zadeklarowana wartością'),
                                                       array('id' => '10', 'text' => 'Pocztex'),
                                                       array('id' => '12', 'text' => 'Pocztex kurier 48 (przesyłka biznesowa)'),
                                                       array('id' => '16', 'text' => 'Pocztex 2.0'),
                                                       array('id' => '13', 'text' => 'Przesyłka firmowa polecona'),
                                                       array('id' => '14', 'text' => 'Usługa paczkowa'),
                                                       array('id' => '23', 'text' => 'GLOBAL Expres'),
                                                       array('id' => '20', 'text' => 'Zagraniczna przesyłka polecona'),
                                                       array('id' => '22', 'text' => 'Zagraniczna paczka do Unii Europejskiej')
                                        );
                                        echo Funkcje::RozwijaneMenu('typ_wysylki', $tablica, $domyslnie, 'id="typ_wysylki" style="width:300px;"' ); 
                                        unset($tablica);
                                        ?>
                                    </p> 

                                    <div id="formularz" style="font-weight:normal;"></div>

                                </div>

                            </div>

                            <div class="OknoDodatkowe">

                                <div class="poleForm">

                                    <div class="naglowek">Informacje</div>

                                    <p>
                                        <label class="readonly">Forma dostawy w zamówieniu:</label>
                                        <input type="text" size="34" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                                    </p> 
                                    <?php
                                    if ( $zamowienie->info['wysylka_info'] != '' ) {
                                        ?>
                                        <p>
                                            <label class="readonly">Punkt odbioru:</label>
                                            <textarea cols="30" rows="2" name="punkt_odbioru" id="punkt_odbioru"  readonly="readonly" class="readonly"><?php echo $zamowienie->info['wysylka_info']; ?></textarea>
                                        </p>
                                        <?php
                                    }
                                    ?>
                                    <p>
                                        <label class="readonly">Forma płatności w zamówieniu:</label>
                                        <input type="text" size="34" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
                                    </p> 
                                    <p>
                                        <label class="readonly">Wartość zamówienia:</label>
                                        <input type="text" name="wartosc_zamowienia" value="<?php echo $waluty->FormatujCene($zamowienie->info['wartosc_zamowienia_val'], false, $zamowienie->info['waluta']); ?>" readonly="readonly" class="readonly" />
                                    </p> 
                                    <p>
                                        <label class="readonly">Waga produktów [g]:</label>
                                        <input type="text" name="waga_zamowienia" value="<?php echo $waga_produktow; ?>" readonly="readonly" class="readonly" />
                                    </p> 

                                </div>

                                <div class="poleForm">

                                    <div class="naglowek">Informacje o odbiorcy</div>

                                    <p>
                                        <label for="nazwa">Adresat:</label>
                                        <input type="text" size="40" name="wysylka[nazwa]" id="nazwa" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? Funkcje::formatujTekstInput($zamowienie->dostawa['firma']) : $zamowienie->dostawa['nazwa'] ); ?>" class="klient" />
                                    </p> 

                                    <p>
                                        <label for="nazwa1">Adresat 1:</label>
                                        <input type="text" size="40" name="wysylka[nazwa1]" id="nazwa1" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? $zamowienie->dostawa['nazwa'] : '' ); ?>" class="klient" />
                                    </p> 

                                    <p id="AdresOdbiorcy">
                                        <label for="ulica">Ulica:</label>
                                        <input type="text" size="40" name="wysylka[ulica]" id="ulica" value="<?php echo $adres_klienta['ulica']; ?>" class="klient" />
                                    </p> 

                                    <p>
                                        <label for="numerDomu">Numer domu:</label>
                                        <input type="text" size="40" name="wysylka[numerDomu]" id="numerDomu" value="<?php echo $adres_dom_lokal['dom']; ?>" class="klient" />
                                    </p> 

                                    <p>
                                        <label for="numerLokalu">Numer lokalu:</label>
                                        <input type="text" size="40" name="wysylka[numerLokalu]" id="numerLokalu" value="<?php echo $adres_dom_lokal['mieszkanie']; ?>" class="klient" />
                                    </p> 

                                    <p>
                                        <label for="kod">Kod pocztowy:</label>
                                        <input type="text" size="40" name="wysylka[kod]" id="kod" value="<?php echo str_replace('-', '', (string)$zamowienie->dostawa['kod_pocztowy']); ?>" class="klient" />
                                    </p> 

                                    <p>
                                        <label for="miejscowosc">Miejscowość:</label>
                                        <input type="text" size="40" name="wysylka[miejscowosc]" id="miejscowosc" value="<?php echo $zamowienie->dostawa['miasto']; ?>" class="klient" />
                                    </p> 

                                    <p>
                                        <label for="panstwo">Państwo:</label>
                                        <input type="text" size="40" name="wysylka[panstwo]" id="panstwo" value="<?php echo $zamowienie->dostawa['kraj']; ?>" class="klient" />
                                    </p> 

                                    <p>
                                        <label for="telefon">Numer telefonu:</label>
                                        <?php 
                                        if ( $zamowienie->dostawa['telefon'] != '' ) {
                                            $NumerTelefonu = $zamowienie->dostawa['telefon'];
                                        } else {
                                            $NumerTelefonu = $zamowienie->klient['telefon'];
                                        }
                                        ?>
                                        <?php if ( Klienci::CzyNumerGSM($NumerTelefonu) ) { ?>
                                            <input type="text" size="40" name="wysylka[mobile]" id="telefon" value="<?php echo $NumerTelefonu; ?>" class="klient" />
                                        <?php } else { ?>
                                            <input type="text" size="40" name="wysylka[telefon]" id="telefon" value="<?php echo $NumerTelefonu; ?>" class="klient" />
                                        <?php } ?>
                                    </p> 

                                    <p>
                                        <label for="email">Adres e-mail:</label>
                                        <input type="text" size="40" name="wysylka[email]" id="email" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
                                    </p> 

                                </div>

                            </div>

                        </div>

                        <div class="przyciski_dolne">
                            <input type="submit" class="przyciskNon" value="Utwórz przesyłkę" />
                            <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
                        </div>
                    </form>

                    <?php 
                //} else {
                //    echo 'Sprawdź konfigurację modułu';
                //}
                ?>
        
            </div>
        </div>

        <?php 
        } 
        ?>
    
    </div>    
    
    <?php
    include('stopka.inc.php');    
    
} 


?>
