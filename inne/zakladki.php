<?php
mb_internal_encoding("UTF-8");

chdir('../');

require_once('ustawienia/ustawienia_db.php');
 
define('POKAZ_ILOSC_ZAPYTAN', false);
define('DLUGOSC_SESJI', '9000');
define('NAZWA_SESJI', 'eGold');

include 'klasy/Bazadanych.php';
$db = new Bazadanych();

include 'klasy/Sesje.php';
$session = new Sesje((int)DLUGOSC_SESJI);

include 'klasy/CacheJs.php';
$cacheJs = new CacheJs();
$StaleDefinicjeJs = $cacheJs->CacheJsFunc();

if ( isset($_POST['zakladka']) ) {
 
    // facebook
    
    if ( $_POST['zakladka'] == 'ramkaFb' ) {
      
        ?>
        
        <iframe src="https://www.facebook.com/plugins/likebox.php?href=http%3A%2F%2F<?php echo ZAKLADKA_FACEBOOK_PROFIL; ?>&amp;width=300&amp;height=300&amp;show_faces=true&amp;colorscheme=light&amp;stream=false&amp;border_color=%23ffffff&amp;header=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:300px; height:130px;" allowTransparency="true"></iframe>
        
        <?php
      
    }
    
    // gadu gadu
    
    if ( $_POST['zakladka'] == 'ramkaGg' ) {
     
        ?>
        
        <iframe src="https://widget.gg.pl/widget/<?php echo ZAKLADKA_GG_PROFIL; ?>#uin=<?php echo ZAKLADKA_GG_NUMER; ?>|msg_online=<?php echo ZAKLADKA_GG_ONLINE; ?>|msg_offline=<?php echo ZAKLADKA_GG_OFFLINE; ?>|hash=<?php echo ZAKLADKA_GG_PROFIL; ?>" height="350" width="225" frameborder="0"></iframe>
        
        <?php
      
    }
    
    // youtube
    
    if ( $_POST['zakladka'] == 'ramkaYt' ) {
     
        /*        
        <iframe id="fr" src="https://www.youtube.com/subscribe_widget?p=<?php echo ZAKLADKA_YOUTUBE_PROFIL; ?>" style="border:0 none;height:100px;overflow:hidden;width:290px;background:#fff" scrolling="no" frameBorder="0"></iframe>        
        - strona: link https://developers.google.com/youtube/youtube_subscribe_button
        */
        
        ?>

        <script src="https://apis.google.com/js/platform.js"></script>
        
        <?php if ( ZAKLADKA_YOUTUBE_IDENTYFIKATOR == 'nazwa użytkownika' ) { ?>
        
        <div class="g-ytsubscribe" data-channel="<?php echo ZAKLADKA_YOUTUBE_PROFIL; ?>" data-theme="dark" data-layout="full" data-count="default"></div>
        
        <?php } else { ?>
        
        <div class="g-ytsubscribe" data-channelid="<?php echo ZAKLADKA_YOUTUBE_PROFIL; ?>" data-layout="full" data-theme="dark" data-count="default"></div>
        
        <?php } ?>

        <?php
        
    }    

    // twitter
    
    if ( $_POST['zakladka'] == 'ramkaTw' ) {
     
        /*
        <script>
        !function(d,s,id){
        var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';
        if(!d.getElementById(id)){
        js=d.createElement(s);
        js.id=id;
        js.src=p+"://platform.twitter.com/widgets.js";
        fjs.parentNode.insertBefore(js,fjs);
        }
        }
        (document,"script","twitter-wjs");  
        </script>
        
        <a class="twitter-timeline" width="302" height="300" data-dnt="true" href="https://twitter.com/<?php echo ZAKLADKA_TWITTER_PROFIL; ?>" data-widget-id="<?php echo ZAKLADKA_TWITTER_WIDGET; ?>">&nbsp;</a>
        */
        
        ?>
        
        <div style="margin:3px 3px 0px 3px">
        
            <a class="twitter-timeline" data-width="294" data-height="380" data-theme="light" href="https://twitter.com/<?php echo ZAKLADKA_TWITTER_PROFIL; ?>">Tweets by <?php echo ZAKLADKA_TWITTER_PROFIL; ?></a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
            
        </div>        

        <?php
      
    }       
    
    // instagram
    
    if ( $_POST['zakladka'] == 'ramkaIn' ) {
      
        ?>
        
        <?php
        /*
        <iframe src="https://widget.websta.me/in/<?php echo ZAKLADKA_INSTAGRAM_PROFIL; ?>/?s=110&w=2&h=3&b=0&bg=FFFFFF&p=5" allowtransparency="true" frameborder="0" scrolling="no" style="border:none;overflow:hidden;width:230px; height:345px"></iframe> <!-- websta - websta.me -->
        */
        ?>
        
        <iframe src="https://snapwidget.com/embed/<?php echo ZAKLADKA_INSTAGRAM_PROFIL; ?>" class="snapwidget-widget" allowTransparency="true" frameborder="0" scrolling="no" style="border:none; overflow:hidden; width:230px; height:345px"></iframe>
        
        <?php
        
    }
    
    // pinterest
    
    if ( $_POST['zakladka'] == 'ramkaPinte' ) {
      
        ?>
        
        <script async defer src="//assets.pinterest.com/js/pinit.js"></script>
        
        <?php if ( ZAKLADKA_PINTEREST_WIDGET == 'widget tablicy' ) { ?>
        
        <a data-pin-do="embedBoard" data-pin-board-width="306" data-pin-scale-height="400" data-pin-scale-width="<?php echo ZAKLADKA_PINTEREST_ZDJECIA; ?>" href="https://www.pinterest.com/<?php echo ZAKLADKA_PINTEREST_PROFIL; ?>/"></a>
        
        <?php } else { ?>
        
        <a data-pin-do="embedUser" data-pin-board-width="306" data-pin-scale-height="400" data-pin-scale-width="<?php echo ZAKLADKA_PINTEREST_ZDJECIA; ?>" href="https://www.pinterest.com/<?php echo ZAKLADKA_PINTEREST_PROFIL; ?>/"></a>
        
        <?php } ?>
        
        <?php
        
    }    
  
}
?> 
