<?php
/**
 * Biblioteka KwotaSlownie
 * 
 * Przede wszystkim potrafi zamienic kwote z postaci liczbowa na postac slowna, 
 * Z ktora na pewno kazdy sie spotkal na rachunkach lub fakturach
 * Biblioteka obsluguje rozne typu danych string, integer, float
 * Dlatego mozna podawac jej kwote z kropka, z przecinkiem lub liczbe calkowita
 * Z kazda z nich biblioteka sobie poradzi wlaczajac w to oczywiscie kwoty ujemne.
 * Biblioteka potrafi takze prawidlowo odmieniac po polsku (tysiac, tysiecy) itd. 
 * Dzieki zapewnionej konfigurowalnosci mozna decydowac czy kwota zdawkowa 
 * Ma byc prezentowana tak jak kwota podstawowa, czy tez w formie liczbowej
 * Mozna takze okreslic w bibliotece walute, gdy potrzebujesz uzyc innej niz polski zloty
 * Bez problemu mozemy nakazac jej odmieniac dolary, euro, jeny czy jakakolwiek inna walute
 * Tyczy sie to takze kwoty zdawkowej czyli grosze, centy, pensy
 * 
 * @link			http://www.kwotaslownie.pl
 * @author		Maciej Strączkowski <m.straczkowski@gmail.com>
 * @copyright	Maciej Strączkowski
 * @category	Libraries
 * @since		06.11.2011
 * @version		1.3 [03.06.2012]
 * @license		LGPL (http://www.kwotaslownie.pl/license.txt)
 */
class KwotaSlownie {
	
	// Wlasciwosc przechowujaca skladowe
	private $aComponents = array();
	
	// Tablica przechowujaca poformatowane czesci kwoty
	private $aOutput = array();
	
	// Czy kwota zdawkowa ma byc takze konwertowana na slowa
	private $bRestWords = true;
	
	// Aktualna wersja aplikacji
	static public $sVersion = '1.3';
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda __construct();
	 * 
	 * Metoda tworzy wlasciwosc prywatna, ktora jest tablica
	 * Zawiera ona czesci skladowe cen w postaci slownej
	 * Te dane sa zapisywane jako wlasciwosc, aby miec do nich
	 * Dostep w obrebie calej projektowanej biblioteki
	 * 
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
    if ( $_SESSION['domyslnyJezyk']['id'] == '1' ) {
      $this->aComponents = array(
        'unities' => array(
          'zero', 'jeden', 'dwa', 'trzy', 'cztery', 'pięć', 
          'sześć', 'siedem', 'osiem', 'dziewięć', 'dziesięć', 
          'jedenaście', 'dwanaście', 'trzynaście', 'czternaście', 
          'piętnaście', 'szesnaście', 'siedemnaście', 'osiemnaście', 
          'dziewiętnaście'
         ),
        'tens' => array(
          '', 'dziesięć', 'dwadzieścia', 'trzydzieści', 'czterdzieści', 
          'pięćdziesiąt', 'sześćdziesiąt', 'siedemdziesiąt', 'osiemdziesiąt', 
          'dziewięćdziesiąt'
         ),
        'hundreds' => array(
          '', 'sto', 'dwieście', 'trzysta', 'czterysta', 
          'pięćset', 'sześćset', 'siedemset', 'osiemset', 
          'dziewięćset'
         ),
        'thousands' => array(
          'tysiąc', 'tysiące', 'tysięcy'
         ),
        'milions' => array(
          'milion', 'miliony', 'milionów'
         ),
        'billions' => array(
          'miliard', 'miliardy', 'miliardów'
         ),
        'currency' => array(
           'złoty', 'złote', 'złotych'
         ),
        'currency_rest' => array(
          'grosz', 'grosze', 'groszy'
         )
      );
    } else {
      $this->aComponents = array(
        'unities' => array(
          'zero', 'one', 'two', 'three', 'four', 'five', 
          'six', 'seven', 'eight', 'nine', 'ten', 
          'eleven', 'twelve', 'thirteen', 'fourteen', 
          'fifteen', 'sixteen', 'seventeen', 'eighteen', 
          'nineteen'
         ),
        'tens' => array(
          '', 'ten', 'twenty', 'thirty', 'forty', 
          'fifty', 'sixty', 'seventy', 'eighty', 
          'ninety'
         ),
        'hundreds' => array(
          '', 'one hundred', 'two hundred', 'three hundred', 'four hundred', 
          'five hundred', 'six hundred', 'seven hundred', 'eight hundred', 
          'nine hundred'
         ),
        'thousands' => array(
          'thousand', 'thousand', 'thousand'
         ),
        'milions' => array(
          'million', 'million', 'million'
         ),
        'billions' => array(
          'billion', 'billion', 'billion'
         ),
        'currency' => array(
           'euro', 'euro', 'euro'
         ),
        'currency_rest' => array(
          'cent', 'cent', 'cent'
         )
      );


    }
	}//end of __construct() method
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda setCasualMode();
	 * 
	 * Metoda pozwala ustawic czy kwota zdawkowa ma byc konwertowana
	 * Tak samo jak kwota podstawowa na postac slowna
	 * Czy tez ma byc konwertowana na postac liczbowa (np. 10/100)
	 * Jezeli zostanie podana wartosc inna wartosc niz (text lub number)
	 * Zostanie ustawiona wartosc domyslna czyli konwersja slowna
	 * 
	 * @access	public
	 * @param	string	$sMode - text (slownie) lub number (liczbowo)
	 * @return	boolean
	 */
	public function setCasualMode($sMode = 'text')
	{
		switch($sMode)
		{
			case 'text':	$this->bRestWords = true;	break;
			case 'number':	$this->bRestWords = false;	break;
			default:			$this->bRestWords = true;	break;
		}
		return true;
	}//end of setCasualMode() method

// --------------------------------------------------------------------
	
	/**
	 * Metoda setCurrency();
	 * 
	 * Metoda pozwala na reczne ustawienie waluty przez uzytkownika
	 * Mozna zdefiniowac inna walute niz domyslne zlotowki/grosze
	 * Nalezy przekazac metodzie dwa parametry pierwszy jest tablica 
	 * Zawierajaca odmiany waluty kwoty podstawowej, drugi jest tablica
	 * Zawierajaca odmiany waluty kwoty zdawkowej, czyli dla przykladu 
	 * $aPrimary = array('dolar', 'dolary', 'dolarów');
	 * $aSecondary = array('cent', 'centy', 'centów');
	 * 
	 * @access	public
	 * @param	array		$aPrimary	- Tablica z odmianami waluty kwoty podstawowej
	 * @param	array		$aSecondary - Tablica z odmianami waluty kwoty zdawkowej
	 * @return	boolean 
	 */
	public function setCurrency($waluta)
	{

    if ( $_SESSION['domyslnyJezyk']['id'] == '1' ) {
      if ( $waluta == 'GBP' ) {
        $aPrimary = array('funt', 'funty', 'funtów');
        $aSecondary = array('pens', 'pensy', 'pensów');
      } elseif ( $waluta == 'USD' ) {
        $aPrimary = array('dolar', 'dolary', 'dolarów');
        $aSecondary = array('cent', 'centy', 'centów');
      } elseif ( $waluta == 'EUR' ) {
        $aPrimary = array('euro', 'euro', 'euro');
        $aSecondary = array('cent', 'centy', 'centów');
      } else {
        $aPrimary = array('złoty', 'złote', 'złotych');
        $aSecondary = array('grosz', 'grosze', 'groszy');
      }
    } else {
      if ( $waluta == 'GBP' ) {
        $aPrimary = array('pound', 'pounds', 'pound');
        $aSecondary = array('pens', 'pens', 'pens');
      } elseif ( $waluta == 'USD' ) {
        $aPrimary = array('dollar', 'dollars', 'dollar');
        $aSecondary = array('cent', 'cents', 'cent');
      } elseif ( $waluta == 'EUR' ) {
        $aPrimary = array('euro', 'euro', 'euro');
        $aSecondary = array('cent', 'cent', 'cent');
      } else {
        $aPrimary = array('złoty', 'złote', 'złotych');
        $aSecondary = array('grosz', 'grosze', 'groszy');
      }
    }

		$this->aComponents['currency'] = $aPrimary;
		$this->aComponents['currency_rest'] = $aSecondary;
		return true;
	}//end of setCurrency()
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda convertPrice();
	 * 
	 * Metoda dokonuje konwersji przekazanej kwoty z postaci liczbowej
	 * Na postac slowna, uzywajac do tego celu metod prywatnych
	 * Automatycznie zamienia przecinki na kropki oraz zaokragla kwote
	 * Do dwoch miejsc po przecinku
	 * 
	 * @access	public
	 * @param	integer	$iPrice	- Kwota do zamiany
	 * @return	string	- Kwota przedstawiona w postaci slownej
	 */
	public function convertPrice($iPrice)
	{
		$iPrice = str_replace(',', '.', (string)$iPrice);
			if(!is_numeric($iPrice)){
				return '';
			}
			$iPrice = number_format($iPrice, 2, '.', '');
			if($iPrice >= 1000000000000 || $iPrice <= -1000000000000){
				return '';
			}
			if($iPrice < 0){
				$this->aOutput[] = 'minus';
				$iPrice = $iPrice*-1;
				$iPrice = number_format($iPrice, 2, '.', '');
			}
		$aParts = explode('.', (string)$iPrice);
		$iFirst = $aParts[0];
			if(isset($aParts[1]) && $aParts[1] == '00'){
				unset($aParts[1]);
			}
			if(isset($aParts[1])){
				$iSecond = $aParts[1];
				if(strlen((string)$iSecond) < 2){
					$iSecond = $iSecond.'0';
				}
			}
			else {
				$iSecond = 0;
			}
		$this->_convertRouter($iFirst);
		$this->_convertVariety($iFirst, 'currency');
			if($this->bRestWords === true){
				$this->_convertRouter($iSecond);
			}
			else {
				$this->aOutput[] = $iSecond.'/100';
			}
		$this->_convertVariety($iSecond, 'currency_rest');
		$sReturn = implode(' ', (array)$this->aOutput);
		unset($this->aOutput);
		return $sReturn;
	}//end of convertPrice() method.
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda _convertRouter();
	 * 
	 * Metoda okresla ilosc znakow wystepujacych w przekazanej kwocie
	 * Na jej podstawie decyduje co w danej chwili trzeba konwertowac
	 * Ilosc znakow > 9 - konwertuj miliardy
	 * Ilosc znakow >= 7 - konwertuj miliony
	 * Ilosc znakow >= 4 - konwertuj tysiace
	 * Ilosc znakow >= 3 - konwertuj setki
	 * Ilosc znakow >= 2 - konwertuj dziesiatki
	 * Ilosc znakow >= 1 - konwertuj jednostki
	 * 
	 * @access	private
	 * @param	integer	$iPrice	- Kwota do zamiany
	 * @return	boolean
	 */
	private function _convertRouter($iPrice)
	{
		$iLenght = strlen((string)$iPrice);
		if($iLenght > 9){
			$this->_convertBillions($iPrice, $iLenght);
			return true;
		}
		elseif($iLenght >= 7) {
			$this->_convertMilions($iPrice, $iLenght);
			return true;
		}
		elseif($iLenght >= 4) {
			$this->_convertThousands($iPrice, $iLenght);
			return true;
		}
		elseif($iLenght >= 3) {
			$this->_convertHundreds($iPrice);
			return true;
		}
		elseif($iLenght >= 2) {
			$this->_convertTens($iPrice);	
			return true;
		}
		elseif($iLenght >= 1) {
			$this->_convertUnities($iPrice);
			return true;
		}
		return false;
	}//end of _convertRouter() method.
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda _convertBillions();
	 * 
	 * Metoda zapisuje ilosc znakow wystepujacych w przekazanej kwocie
	 * Na jej podstawie decyduje jakie czesci trzeba obciac za pomoca substr
	 * Obciete czesci kwoty ponownie sa wysylana do routera
	 * Dodatkowo dobierana jest poprawna odmiana slowa "miliard"
	 * 
	 * @access	private
	 * @param	integer	$iPrice	- Kwota
	 * @param	integr	$iLength	- Dlugosc
	 * @return	boolean
	 */
	private function _convertBillions($iPrice, $iLength)
	{
		if($iLength >= 12) {
			$iSliced = substr((string)$iPrice, -12, 3);
			$iNextSliced = substr((string)$iPrice, 3, 12);
		}
		elseif($iLength >= 11) {
			$iSliced = substr((string)$iPrice, -11, 2);
			$iNextSliced = substr((string)$iPrice, 2, 11);
		}
		elseif($iLength >= 10) {
			$iSliced = substr((string)$iPrice, -10, 1);
			$iNextSliced = substr((string)$iPrice, 1, 10);
		}
		else {
			return false;
		}
		
		if($iSliced != 1){
			$this->_convertRouter($iSliced);
		}
		if($iSliced != 0){
			$this->_convertVariety($iSliced, 'billions');
		}
		$this->_convertRouter($iNextSliced);
		return true;
	}//end of _convertBillions() method.
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda _convertMilions();
	 * 
	 * Metoda zapisuje ilosc znakow wystepujacych w przekazanej kwocie
	 * Na jej podstawie decyduje jakie czesci trzeba obciac za pomoca substr
	 * Obciete czesci kwoty ponownie sa wysylana do routera
	 * Dodatkowo dobierana jest poprawna odmiana slowa "milion"
	 * 
	 * @access	private
	 * @param	integer	$iPrice	- Kwota
	 * @param	integr	$iLength	- Dlugosc
	 * @return	boolean
	 */
	private function _convertMilions($iPrice, $iLength)
	{
		if($iLength >= 9) {
			$iSliced = substr((string)$iPrice, -9, 3);
			$iNextSliced = substr((string)$iPrice, 3, 9);
		}
		elseif($iLength >= 8) {
			$iSliced = substr((string)$iPrice, -8, 2);
			$iNextSliced = substr((string)$iPrice, 2, 8);
		}
		elseif($iLength >= 7) {
			$iSliced = substr((string)$iPrice, -7, 1);
			$iNextSliced = substr((string)$iPrice, 1, 7);
		}
		else {
			return false;
		}
		
		if($iSliced != 1){
			$this->_convertRouter($iSliced);
		}
		if($iSliced != 0){
			$this->_convertVariety($iSliced, 'milions');
		}
		$this->_convertRouter($iNextSliced);
		return true;
	}//end of _convertMilions() method.
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda _convertThousands();
	 * 
	 * Metoda zapisuje ilosc znakow wystepujacych w przekazanej kwocie
	 * Na jej podstawie decyduje jakie czesci trzeba obciac za pomoca substr
	 * Obciete czesci kwoty ponownie sa wysylana do routera
	 * Dodatkowo dobierana jest poprawna odmiana slowa "tysiac"
	 * 
	 * @access	private
	 * @param	integer	$iPrice	- Kwota
	 * @param	integr	$iLength	- Dlugosc
	 * @return	boolean
	 */
	private function _convertThousands($iPrice, $iLength)
	{
		if($iLength >= 6) {
			$iSliced = substr((string)$iPrice, -6, 3);
			$iNextSliced = substr((string)$iPrice, 3, 6);
		}
		elseif($iLength >= 5) {
			$iSliced = substr((string)$iPrice, -5, 2);
			$iNextSliced = substr((string)$iPrice, 2, 5);
		}
		elseif($iLength >= 4) {
			$iSliced = substr((string)$iPrice, -4, 1);
			$iNextSliced = substr((string)$iPrice, 1, 4);
		}
		else {
			return false;
		}

		if($iSliced != 1){
			$this->_convertRouter($iSliced);
		}
		if($iSliced != 0){
			$this->_convertVariety($iSliced, 'thousands');
		}
		$this->_convertRouter($iNextSliced);
		return true;
	}//end of _convertThousands() method.
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda _convertHundreds();
	 * 
	 * Metoda wycina pierwszy znak liczby, ktora jest setka
	 * I wstawia go jako index tablicy skladowych "hundreds"
	 * Przyklad: 200 - 2 - hundreds[2] - dwiescie
	 * Nastepnie sprawdzane sa kolejne znaki przez substr
	 * 
	 * @access	private
	 * @param	integer	$iPrice - Kwota
	 * @return	boolean
	 */
	private function _convertHundreds($iPrice)
	{
		$iIndex = substr((string)$iPrice, -3, 1);
		$this->aOutput[] = $this->aComponents['hundreds'][$iIndex];
			if(substr((string)$iPrice, 1, 2) > 0){
				$this->_convertRouter(substr((string)$iPrice, 1, 2));
			}
			else {
				$this->_convertTens(substr((string)$iPrice, 1, 2));
			}
		return true;
	}//end of _convertHundreds() method.
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda _convertTens();
	 * 
	 * Metoda sprawdza czy podanej kwoty nie mozna dopasowac do jednostek
	 * Jezeli nie mozna, wycinany jest pierwszy znak kwoty
	 * I wstawiany jest jako index tablicy skladowych "tens"
	 * Kolejny znak jest wysylany znowu do routera
	 * 
	 * @access	private
	 * @param	integer	$iPrice - Kwota
	 * @return	boolean
	 */
	private function _convertTens($iPrice)
	{
		if( isset($iPrice) && array_key_exists($iPrice, $this->aComponents['unities']) && substr((string)$iPrice, 1, 2) != 0){
			$this->aOutput[] = $this->aComponents['unities'][$iPrice];
			return true;
		}
		$iIndex = substr((string)$iPrice, 0, 1);
		$this->aOutput[] = $this->aComponents['tens'][$iIndex];
		if(substr((string)$iPrice, 1, 2) != 0){
			$this->_convertRouter(substr((string)$iPrice, 1, 2));
		}
		return true;
	}//end of _convertTens() method.
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda _convertUnities();
	 * 
	 * Metoda wstawia otrzymana liczbe jako index tablicy "unities"
	 * Dzieki temu wiadomo na jakie slowo zamienic dana liczbe
	 * 1 - unities[1] - jeden, 2 - unities[2] - dwa itd
	 * 
	 * @access	private
	 * @param	integer	$iPrice	- Kwota
	 * @return	boolean
	 */
	private function _convertUnities($iPrice)
	{
		$this->aOutput[] = $this->aComponents['unities'][$iPrice];
		return true;
	}//end of _convertUnities() method.
	
// --------------------------------------------------------------------
	
	/**
	 * Metoda _convertVariety();
	 * 
	 * Metoda na podstawie otrzymanego typu i kwoty
	 * Decyduje o prawidlowej polskiej odmianie
	 * Typ jest niczym innym jak indexem tablicy skladowych
	 * Przyklad: currency, thousands, bilions, milions
	 * 
	 * @access	private
	 * @param	integer	$iPrice	- Kwota
	 * @param	boolean	$sType	- Typ
	 * @return	boolean
	 */
	private function _convertVariety($iPrice, $sType)
	{
		if($iPrice > 9){
			$iLastIntegers = substr((string)$iPrice, -2);
			$sOneCurrency = $this->aComponents[$sType][2];
		}
		else {
			$iLastIntegers = substr((string)$iPrice, -1);
			$sOneCurrency = $this->aComponents[$sType][0];
		}

		if($iLastIntegers >= 15){
			$iLastIntegers = substr((string)$iLastIntegers, 1, 2);
		}
		
		if($iLastIntegers >= 11){
			$this->aOutput[] = $this->aComponents[$sType][2];
		}
		elseif($iLastIntegers == 0)  {
			$this->aOutput[] = $this->aComponents[$sType][2];
		}
		elseif($iLastIntegers == 1)  {
			$this->aOutput[] = $sOneCurrency;
		}
		elseif($iLastIntegers >= 5)  {
			$this->aOutput[] = $this->aComponents[$sType][2];
		}
		elseif($iLastIntegers >= 2)  {
			$this->aOutput[] = $this->aComponents[$sType][1];
		}
		return true;
	}//end of _convertVariety() method
	
}//end of KwotaSlownie Library
?>