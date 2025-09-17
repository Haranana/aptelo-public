<?php

abstract class label extends tcpdf {

  protected $configFile = "labels.xml";
  protected $idLabel;
  protected $labelName;
  protected $labelDescription;
  protected $labelBrand;
  protected $labelSupplier;
  protected $labelWidth;
  protected $labelHeight;
  protected $labelMargin;
  protected $sheetFormat;
  protected $sheetOrientation;
  public $border;
  protected $borderColor;
  protected $borderWidth;
  protected $labelSheetCols = '';
  protected $labelSheetRows;
  protected $labelSheetTopMargin;
  protected $labelSheetLeftMargin;
  protected $data;

  private $nb_rows;
  private $nb_pages;
  private $h_Marge;
  private $v_Marge;

  function __construct($label_id, $data, $border = false) {

    $this->loadLabelConfig($label_id);
    $this->data = $data;
    $this->border = $border;

    tcpdf::__construct($this->sheetOrientation , PDF_UNIT, $this->labelSheetFormat , true);

    $this->ctrlLabelConfig();
    
    $this->SetMargins($this->labelMargin, $this->labelMargin);
    $this->SetCellPadding(0);
  }

  //obsluga bledu
  private function exitLabel($error) {
  
    print "<pre style='background:#faebd7;margin:20px;'>";
    echo "<h2>Blad :</h2>";
    print_r($error);
    print '</pre>';
    die();

  }

  //wczytanie z bazy danych okreslonej etykiety
  private function loadLabelConfig($label_id) {
    global $db;

    $zapytanie = "SELECT * FROM print_labels WHERE id = '".$label_id."'";
    $sql = $db->open_query($zapytanie);
    if ( (int)$db->ile_rekordow($sql) > 0 ) {

      $info = $sql->fetch_assoc();

      $this->labelName = strval($info['name']);
      $this->labelDescription = strval($info['description']);
      $this->labelBrand = strval($info['brand']);
      $this->labelSupplier = strval($info['supplier']);
      $this->labelWidth = floatval($info['width']);
      $this->labelHeight = floatval($info['height']);
      $this->labelMargin = intval($info['margin']);

      $this->labelSheetFormat = strval($info['format']);
      $this->sheetOrientation = strval($info['orientation']);
      $this->borderColor = strval($info['bordercolor']);
      $this->borderWidth = strval($info['borderwidth']);

      $this->labelSheetCols = strval($info['kolumny']);
      $this->labelSheetRows = strval($info['wiersze']);
      $this->labelSheetTopMargin = strval($info['topmargin']);
      $this->labelSheetLeftMargin = strval($info['leftmargin']);

      $db->close_query($sql);
      unset($zapytanie, $info);

      return;

    } else {
      $this->exitLabel('Blad : nie ma takiej etykiety w bazie danych.');
    }

  }

  //sprawdzenie poprawnosci ustawien drukowanej etykiety
  private function ctrlLabelConfig(){

    $error = '';
    if ( ceil($this->getPageWidth()*10000)  < ceil(( ($this->labelSheetCols * $this->labelWidth) + $this->labelSheetLeftMargin)*10000) ){
      $this->exitLabel("<b>Niepoprawna konfiguracja etykiety</b> : podane wymiary etykiet oraz ilosc kolumn (".$this->labelSheetCols." x ".$this->labelWidth.") + ".$this->labelSheetLeftMargin.") sa wieksze niz rozmiar strony do wydruku - szerokosc: (".$this->getPageWidth().") - orientacja: ".$this->sheetOrientation.".");
    }

    if ( ceil($this->getPageHeight()*10000)  < ceil(( ($this->labelSheetRows * $this->labelHeight) + $this->labelSheetTopMargin)*10000) ){
      $this->exitLabel("<b>Niepoprawna konfiguracja etykiety</b> : podane wymiary etykiet oraz ilosc kolumn (".$this->labelSheetRows." x ".$this->labelHeight.") + ".$this->labelSheetTopMargin.") sa wieksze niz rozmiar strony do wydruku - wysokosc: (".$this->getPageHeight().") - orientacja: ".$this->sheetOrientation.".");
    }

  }

  private function Affichage(){
    $lg = Array();
    $lg['a_meta_charset'] = "UTF-8";
    $lg['a_meta_dir'] = "ltr";
    $lg['a_meta_language'] = "pl";
    $this->setLanguageArray($lg); 
  }

  public function AddLabel(){
  
    // Ilosc etykiet do wydrukowania
    $nb_el = count($this->data);

    // Ilosc linii
    $this->nb_rows = ceil($nb_el / $this->labelSheetCols);
    
    // Ilosc stron
    $this->nb_pages = ceil($this->nb_rows / $this->labelSheetRows);

    // Obliczanie poziomego odstepu pomiedzy etykietami
    if ($this->labelSheetCols <= 1){ $nb_space = 1; } else { $nb_space  = $this->labelSheetCols - 1; }
    $this->h_Marge = ($this->getPageWidth() -  ($this->labelSheetCols * $this->labelWidth) - (2 * $this->labelSheetLeftMargin) ) / $nb_space;

    // Obliczanie pionowego odstepu pomiedzy etykietami
    if ($this->nb_rows <= 1){ $nb_space = 1;  } else { $nb_space  = $this->nb_rows - 1; }
    $this->v_Marge = ($this->getPageHeight() -  ($this->labelSheetRows * $this->labelHeight) - (2 * $this->labelSheetTopMargin) ) / $nb_space;

    // Ustawienie strony kodowej i jezyka
    $this->Affichage();

    $this->posLoop();

  }

  public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false){
  
    $this->SetMargins($this->labelMargin, $this->labelMargin);
    $this->SetCellPadding(0);
    tcpdf::AddPage();
    
  }

  private function posLoop(){
      
    // Compteur element
    $n=0;
    
    //echo "nb lignes : ".$this->nb_rows." - nb pages : ".$this->nb_pages."<br/>";
    
    for ($k=0; $k < $this->nb_pages; $k++) {

      $this->AddPage();
      $x = 0;
      $y = 0;

      // Petla dla wiersza
      for ($j=0; $j < $this->labelSheetRows; $j++) {
 
        $y = $this->labelSheetTopMargin + ( $j * $this->labelHeight) + ($j * $this->v_Marge);
  
        // Petla w wierszu
        for ( $i=0 ; $i< $this->labelSheetCols; $i++ ){

          $x = $this->labelSheetLeftMargin+ ( $i  * $this->labelWidth) + ($i * $this->h_Marge);

          if ($n < count($this->data)) {

            if($this->border && !is_null($this->data[$n]) && strlen(implode('', (array)$this->data[$n])) != 0) {
              //$this->borderColor = '#FF0000';
              $color_array = TCPDF_COLORS::convertHTMLColorToDec('#'.$this->borderColor);
              $borderstyle = array('width' => $this->borderWidth, 'cap' => 'butt', 'join' => 'miter', 'dash' => '', 'color' => array($color_array['R'], $color_array['G'], $color_array['B']));
              $this->Rect($x, $y, $this->labelWidth, $this->labelHeight, "D", array('all'=>$borderstyle ) ); 
            }

            if ( !is_null( $this->data[$n]) ){
              $this->template($x, $y, $this->data[$n] );
            }
          }
          $n++;
        }
      }

    }
  
  }

  abstract function template($x, $y, $dataPrint);


  public function Output($name='', $dest='' ){
    tcpdf::Output($name, $dest);
  }

}

?>