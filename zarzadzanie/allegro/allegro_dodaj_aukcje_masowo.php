<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( Funkcje::SprawdzAktywneAllegro() ) {
        $AllegroRest = new AllegroRest( array('allegro_user' => $_SESSION['domyslny_uzytkownik_allegro']) );
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    
    <div class="poleForm cmxform" style="margin-bottom:10px">
        <div class="naglowek">Ustawienia konfiguracji połączenia z Allegro</div>

        <div class="pozycja_edytowana">
              
            <?php require_once('allegro_naglowek.php'); ?>
                
        </div>
    </div>    
        
    <div id="cont">
          
        <form action="allegro/allegro_dodaj_aukcje_masowo_import.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Pobieranie danych z Allegro</div>
            
            <div class="pozycja_edytowana">
            
                <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
                
                <?php
                $NazwaKonta = '';
                if ( isset($_SESSION['domyslny_uzytkownik_allegro']) ) {
                     $NazwaKonta = $_SESSION['domyslny_login_allegro'];
                }                
                ?>
                
                <input type="hidden" name="akcja" value="zapisz" />

                <p>
                  Czy zaimportować wszystkie aukcje dla użytkownika <b><?php echo $NazwaKonta; ?></b> oraz dodać na ich podstawie produkty do sklepu ?
                </p>   
                
                <span class="ostrzezenie" style="margin:12px 0px 5px 9px">Do sklepu zostaną dodane wszystkie aukcje zalogowanego użytkownika oraz na ich podstawie zostaną w sklepie utworzone nowe produkty. <br /> Przed importem zalecamy wykonanie archwizacji bazy danych w menu Narzędzia / Archiwizacja danych.</span>  
                
                <?php } else { ?>
                
                <span class="ostrzezenie" style="margin:10px">Brak połączenia z Allegro</span>
                
                <?php } ?>
             
            </div>

            <div class="przyciski_dolne">
              <?php if ( Funkcje::SprawdzAktywneAllegro() ) { ?>
              <input type="submit" class="przyciskNon" value="Pobierz dane" />
              <?php } ?>
              <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
            </div>


          </div>   
          
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}