<?php
// jezeli jest sortowanie
$sortowanie = $GLOBALS['DomyslneSortowanie'];
//
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case "sort_a17":
            $sortowanie = 'pd.products_name asc, p.products_id';
            break;
        case "sort_a2":
            $sortowanie = 'pd.products_name desc, p.products_id';
            break;
        case "sort_a7":
            $sortowanie = 'p.products_model asc, p.products_id';
            break;
        case "sort_a8":
            $sortowanie = 'p.products_model desc, p.products_id';
            break;  
        case "sort_a9":
            $sortowanie = 'p.products_price_tax asc, p.products_id';
            break;
        case "sort_a10":
            $sortowanie = 'p.products_price_tax desc, p.products_id';
            break;  
        case "sort_a11":
            $sortowanie = 'p.products_quantity asc, p.products_id';
            break;
        case "sort_a12":
            $sortowanie = 'p.products_quantity desc, p.products_id';
            break;     
        case "sort_a13":
            $sortowanie = 'p.products_id desc';
            break;
        case "sort_a14":
            $sortowanie = 'p.products_id asc';
            break;                          
        case "sort_a3":
            $sortowanie = 'p.products_status desc, pd.products_name, p.products_id';
            break;  
        case "sort_a4":
            $sortowanie = 'p.products_status asc, pd.products_name, p.products_id';
            break;
        case "sort_a5":
            $sortowanie = 'p.products_date_added desc, pd.products_name, p.products_id';
            break; 
        case "sort_a6":
            $sortowanie = 'p.products_date_added asc, pd.products_name, p.products_id';
            break;    
        case "sort_a15":
            $sortowanie = 'p.sort_order desc, p.products_id';
            break;
        case "sort_a16":
            $sortowanie = 'p.sort_order asc, p.products_id';
            break;             
    }            
}    

$zapytanie .= (($sortowanie != '') ? " order by ".$sortowanie : ''); 
?>