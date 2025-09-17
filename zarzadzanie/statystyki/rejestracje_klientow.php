<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Wykres rejestracji klientów</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje rejestracje klientów w sklepie w określonych przedziałach czasowych</span>
                    
                    <?php
                    $Okres = '30';
                    ?>

                    <div class="WykresStatystki">
                    
                        <h3>
                            <?php
                            echo 'Rejestracje klientów za ostatnie ' . $Okres . ' dni';
                            ?>
                        </h3>                    
                    
                        <div class="SzerokiWykres"><canvas id="klienci_30_dni" width="800" height="230"></canvas></div>
                        
                    </div>    

                    <?php
                    include('statystyki/rejestracje_klientow_wykres_dzienny.php');
                    //
                    unset($Okres);
                    ?>                    

                    <br /><br />
                    
                    <?php
                    $Okres = '24';
                    ?>
                    
                    <div class="WykresStatystki">
                    
                        <h3>
                            <?php
                            echo 'Rejestracje klientów za ostatnie ' . $Okres . ' miesiące';
                            ?>
                        </h3>                    
                    
                        <div class="SzerokiWykres"><canvas id="klienci_miesiace" width="800" height="230"></canvas></div>
                        
                    </div>    

                    <?php
                    include('statystyki/rejestracje_klientow_wykres_miesieczny.php');
                    //
                    unset($Okres);
                    ?>        

                    <script src="statystyki/rejestracje_klientow.js"></script>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}