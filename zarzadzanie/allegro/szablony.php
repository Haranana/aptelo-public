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
    
    <div>
    
        <div id="naglowek_cont">Szablony aukcji Allegro</div>     

        <div style="clear:both;"></div>   

        <?php
        $zapytanie = "select distinct * from allegro_theme";
        $sql = $db->open_query($zapytanie); 

        echo '<div class="DodanieSzablonu">';
        
            echo '<a href="allegro/szablony_nowy_dodaj_edytuj.php?id_nowy=nowy" class="dodaj">dodaj nowy szablon</a>';
            
        echo '</div>';
        
        if ((int)$db->ile_rekordow($sql) > 0) {

            echo '<div id="Szablony">';

            while ($info = $sql->fetch_assoc()) {
              
                echo '<div class="PodgladSzablonu">';
                
                echo '<a class="PodgladScreenNowy" href="allegro/szablony_nowy_dodaj_edytuj.php?id_nowy=' . $info['allegro_theme_id'] . '"></a>';
                                    
                echo '<div>';

                    echo '<a class="TipChmurka" href="allegro/szablony_nowy_dodaj_edytuj.php?id_nowy=' . $info['allegro_theme_id'] . '"><b>Edytuj</b><img src="obrazki/edytuj.png" alt="Edytuj" />' . $info['allegro_theme_name'] . '</a>';
                
                echo '</div>';
                
                echo '<div>';

                    echo '<a class="TipChmurka" href="allegro/szablony_nowy_usun.php?id_nowy=' . $info['allegro_theme_id'] . '"><b>Usuń szablon</b><img src="obrazki/kasuj.png" alt="Kasuj" />usuń szablon</a>';
                
                echo '</div>';                

                echo '</div>';                

            }
            
            echo '</div>';
            
        } else {
         
            echo '<div class="BrakSzablonow"><span class="maleInfo">... brak zdefiniowanych szablonów ...</span></div>';
          
        }
      
        $db->close_query($sql); 
        unset($zapytanie);            

        ?>

        <div style="clear:both;"></div>               

    </div>

    <?php include('stopka.inc.php');

} ?>
