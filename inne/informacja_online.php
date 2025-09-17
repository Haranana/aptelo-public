<?php
// sprawdza czy katalog jest pusty
function czyFolderJestPusty( $folderName ){
  $files = array ();
  if ( $handle = opendir ( $folderName ) ) {
      while ( false !== ( $file = readdir ( $handle ) ) ) {
          if ( $file != "." && $file != ".." && $file != "js" && $file != "index.php" && substr((string)$file,0,6) != 'Cache_' ) {
              $files [] = $file;
          }
      }
      closedir ( $handle );
  }
  unset($folderName);
  return ( count ( $files ) > 0 ) ? true: false;
}
  
chdir('../'); 

if ( isset($_POST['id']) ) {
  
     if ( file_exists( 'cache/online_' . $_POST['id'] ) ) {
       
          // sprawdza plik
          if ( czyFolderJestPusty("cache") ) {
            
              $files = glob('cache/online_*');
              
              if ( !empty($files) ) {
                
                  foreach($files as $file) {
                    
                      $plik = file_get_contents($file);
                      
                      $plik = @unserialize($plik);
                      if (!$plik) {

                          if ( is_file($file) ) {
                              @unlink($file);
                          }

                      }

                      if (time() > $plik[0]) {

                          if ( is_file($file) ) {
                              @unlink($file);
                          }

                      }
              
                  }
                  
              }
              
              unset($files);

          }             
          
          if ( file_exists( 'cache/online_' . $_POST['id'] ) ) {
  
              $info = file_get_contents( 'cache/online_' . $_POST['id'] );
              
              $info = @unserialize($info);
              
              if ( $info != '' ) {
                
                  echo '<div id="PopUpInfo">';

                  echo base64_decode((string)$info[1]);
                  
                  echo '</div>';
                  
              }     

              unset($info);
              
              unlink('cache/online_' . $_POST['id']);
              
          }
  
     }

}  
?>