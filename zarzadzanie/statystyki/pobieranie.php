<?php
dl_file('../../export/export_inwentaryzacja_' . $_GET['plik'] . '.csv');

function dl_file($file){
 
    if (!is_file($file)) { die("<b>404 Nie ma takiego pliku !</b>"); }
     
    $len = filesize($file);
    $filename = basename($file);
    $file_extension = strtolower(substr(strrchr($filename,"."),1));
     
    $moznaPobrac = false;
    
    switch( $file_extension ) {
    case "csv": $ctype="application/vnd.ms-excel"; $moznaPobrac = true; break;
     
    case "php":
    case "htm":
    case "html":
    case "txt": die("<b>Nie mozna pobrac takiego pliku"); break;
     
    default: $ctype="application/force-download";
    }
    
    if ($moznaPobrac == true) {
     
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        
        header("Content-Type: $ctype");
         
        $header="Content-Disposition: attachment; filename=".$filename.";";
        header($header );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$len);
        @readfile($file);
        exit;
        
    } else {
    
        die("<b>Nie mozna pobrac pliku");
        
    }
    
}
 
?>