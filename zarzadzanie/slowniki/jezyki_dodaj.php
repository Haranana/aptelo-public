<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["domyslny"] == '1') {
            $pola = array(array('languages_default','0'));
            $db->update_query('languages' , $pola);
            unset($pola);	        
        }
        //
        $pola = array(
                array('name',$filtr->process($_POST["nazwa"])),
                array('code',$filtr->process($_POST["kod"])),
                array('image',$filtr->process($_POST["zdjecie"])),
                array('sort_order',$_POST["sort"]),
                array('currencies_default',$_POST["domyslna_waluta"]),
                array('languages_default',$_POST["domyslny"]),
                array('status','1')
                );
        //	
        $db->insert_query('languages' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);

        // dodanie rekordow w tablicy tlumaczen
        $zapytanie = "SELECT * FROM translate_value WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('translate_value',$info["translate_value"]),
                    array('translate_constant_id',$info["translate_constant_id"]),
                    array('language_id',$id_dodanej_pozycji)
                    );
            $db->insert_query('translate_value' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);

        //#################################################
        // dodanie rekordow w tablicy categories_description
        $zapytanie = "SELECT * FROM categories_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('categories_id',(int)$info["categories_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('categories_name',$info["categories_name"]),
                    array('categories_description',$info["categories_description"]),
                    array('categories_meta_title_tag',$info["categories_meta_title_tag"]),
                    array('categories_meta_desc_tag',$info["categories_meta_desc_tag"]),
                    array('categories_meta_keywords_tag',$info["categories_meta_keywords_tag"])
                    );
            $db->insert_query('categories_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy complaints_status_description
        $zapytanie = "SELECT * FROM complaints_status_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('complaints_status_id',(int)$info["complaints_status_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('complaints_status_name',$info["complaints_status_name"])
                    );
            $db->insert_query('complaints_status_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy countries_description
        $zapytanie = "SELECT * FROM countries_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('countries_id',(int)$info["countries_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('countries_name',$info["countries_name"])
                    );
            $db->insert_query('countries_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy customers_points_status_description
        $zapytanie = "SELECT * FROM customers_points_status_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('points_status_id',(int)$info["points_status_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('points_status_name',$info["points_status_name"])
                    );
            $db->insert_query('customers_points_status_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy email_templates_description
        $zapytanie = "SELECT * FROM email_templates_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('template_id',(int)$info["template_id"]),
                    array('description',$info["description"]),
                    array('language_id',$id_dodanej_pozycji)
                    );
            $db->insert_query('email_templates_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy email_text_description
        $zapytanie = "SELECT * FROM email_text_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('email_text_id',(int)$info["email_text_id"]),
                    array('email_title',$info["email_title"]),
                    array('description',$info["description"]),
                    array('description_sms',$info["description_sms"]),
                    array('language_id',$id_dodanej_pozycji)
                    );
            $db->insert_query('email_text_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy form_description
        $zapytanie = "SELECT * FROM form_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('id_form',(int)$info["id_form"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('form_name',$info["form_name"]),
                    array('form_title_email',$info["form_title_email"]),
                    array('form_text_email',$info["form_text_email"]),
                    array('template_email_id',$info["template_email_id"]),
                    array('form_description',$info["form_description"]),
                    array('form_email_1',$info["form_email_1"]),
                    array('form_email_name_1',$info["form_email_name_1"]),
                    array('form_email_2',$info["form_email_2"]),
                    array('form_email_name_2',$info["form_email_name_2"]),
                    array('form_email_3',$info["form_email_3"]),
                    array('form_email_name_3',$info["form_email_name_3"]),
                    array('form_email_4',$info["form_email_4"]),
                    array('form_email_name_4',$info["form_email_name_4"]),
                    array('form_email_5',$info["form_email_5"]),
                    array('form_email_name_5',$info["form_email_name_5"]),
                    array('form_meta_title_tag',$info["form_meta_title_tag"]),
                    array('form_meta_desc_tag',$info["form_meta_desc_tag"]),
                    array('form_meta_keywords_tag',$info["form_meta_keywords_tag"])
            );
            $db->insert_query('form_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy form_field
        $zapytanie = "SELECT * FROM form_field WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('id_form',(int)$info["id_form"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('form_field_sort',$info["form_field_sort"]),
                    array('form_field_name',$info["form_field_name"]),
                    array('form_field_typ',$info["form_field_typ"]),
                    array('form_field_value',$info["form_field_value"]),
                    array('form_field_required',$info["form_field_required"]),
                    array('form_field_length',$info["form_field_length"]),
                    array('form_field_input_length',$info["form_field_input_length"]),
                    array('form_field_input_limit',$info["form_field_input_limit"]),
                    array('form_field_email',$info["form_field_email"]),
                    array('form_field_email_header',$info["form_field_email_header"])

            );
            $db->insert_query('form_field' , $pola);
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy gallery_description
        $zapytanie = "SELECT * FROM gallery_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('id_gallery',(int)$info["id_gallery"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('gallery_name',$info["gallery_name"]),
                    array('gallery_description',$info["gallery_description"]),
                    array('gallery_meta_title_tag',$info["gallery_meta_title_tag"]),
                    array('gallery_meta_desc_tag',$info["gallery_meta_desc_tag"]),
                    array('gallery_meta_keywords_tag',$info["gallery_meta_keywords_tag"])
            );
            $db->insert_query('gallery_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy gallery_image
        $zapytanie = "SELECT * FROM gallery_image WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('id_gallery',(int)$info["id_gallery"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('gallery_image',$info["gallery_image"]),
                    array('gallery_image_sort',$info["gallery_image_sort"]),
                    array('gallery_image_description',$info["gallery_image_description"]),
                    array('gallery_image_alt',$info["gallery_image_alt"])
            );
            $db->insert_query('gallery_image' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy headertags
        $zapytanie = "SELECT * FROM headertags WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('page_id',(int)$info["page_id"]),
                    array('page_name',$info["page_name"]),
                    array('page_title',$info["page_title"]),
                    array('page_description',$info["page_description"]),
                    array('page_keywords',$info["page_keywords"]),
                    array('append_default',$info["append_default"]),
                    array('sortorder',$info["sortorder"]),
                    array('language_id',$id_dodanej_pozycji)
            );
            $db->insert_query('headertags' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy headertags_default
        $zapytanie = "SELECT * FROM headertags_default WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('default_title',$info["default_title"]),
                    array('default_description',$info["default_description"]),
                    array('default_keywords',$info["default_keywords"]),
                    array('default_index_title',$info["default_index_title"]),
                    array('default_index_keywords',$info["default_index_keywords"]),
                    array('default_index_description',$info["default_index_description"]),
                    array('og_title',$info["og_title"]),
                    array('og_site_name',$info["og_site_name"]),
                    array('og_description',$info["og_description"]),
                    array('og_image',$info["og_image"]),
                    array('language_id',$id_dodanej_pozycji)
            );
            $db->insert_query('headertags_default' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy newsdesk_categories_description
        $zapytanie = "SELECT * FROM newsdesk_categories_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('categories_id',(int)$info["categories_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('categories_name',$info["categories_name"]),
                    array('categories_description',$info["categories_description"]),
                    array('categories_meta_title_tag',$info["categories_meta_title_tag"]),
                    array('categories_meta_desc_tag',$info["categories_meta_desc_tag"]),
                    array('categories_meta_keywords_tag',$info["categories_meta_keywords_tag"])
            );
            $db->insert_query('newsdesk_categories_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy newsdesk_description
        $zapytanie = "SELECT * FROM newsdesk_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('newsdesk_id',(int)$info["newsdesk_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('newsdesk_article_name',$info["newsdesk_article_name"]),
                    array('newsdesk_article_description',$info["newsdesk_article_description"]),
                    array('newsdesk_article_short_text',$info["newsdesk_article_short_text"]),
                    array('newsdesk_article_viewed',$info["newsdesk_article_viewed"]),
                    array('newsdesk_meta_title_tag',$info["newsdesk_meta_title_tag"]),
                    array('newsdesk_meta_desc_tag',$info["newsdesk_meta_desc_tag"]),
                    array('newsdesk_meta_keywords_tag',$info["newsdesk_meta_keywords_tag"])
            );
            $db->insert_query('newsdesk_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy orders_status_description
        $zapytanie = "SELECT * FROM orders_status_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('orders_status_id',(int)$info["orders_status_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('orders_status_name',$info["orders_status_name"])
            );
            $db->insert_query('orders_status_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy pages_description
        $zapytanie = "SELECT * FROM pages_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('pages_id',(int)$info["pages_id"]),
                    array('pages_title',$info["pages_title"]),
                    array('pages_short_text',$info["pages_short_text"]),
                    array('pages_text',$info["pages_text"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('meta_title_tag',$info["meta_title_tag"]),
                    array('meta_desc_tag',$info["meta_desc_tag"]),
                    array('meta_keywords_tag',$info["meta_keywords_tag"])
            );
            $db->insert_query('pages_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy poll_description
        $zapytanie = "SELECT * FROM poll_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('id_poll',(int)$info["id_poll"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('poll_name',$info["poll_name"]),
                    array('poll_description',$info["poll_description"]),
                    array('poll_meta_title_tag',$info["poll_meta_title_tag"]),
                    array('poll_meta_desc_tag',$info["poll_meta_desc_tag"]),
                    array('poll_meta_keywords_tag',$info["poll_meta_keywords_tag"])
            );
            $db->insert_query('poll_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy products_availability_description
        $zapytanie = "SELECT * FROM products_availability_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('products_availability_id',(int)$info["products_availability_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('products_availability_name',$info["products_availability_name"])
            );
            $db->insert_query('products_availability_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy products_description
        $zapytanie = "SELECT * FROM products_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('products_id',(int)$info["products_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('products_name',$info["products_name"]),
                    array('products_description',$info["products_description"]),
                    array('products_short_description',$info["products_short_description"]),
                    array('products_viewed',$info["products_viewed"]),
                    array('products_meta_title_tag',$info["products_meta_title_tag"]),
                    array('products_meta_desc_tag',$info["products_meta_desc_tag"]),
                    array('products_meta_keywords_tag',$info["products_meta_keywords_tag"]),
                    array('products_seo_url',$info["products_seo_url"])
            );
            $db->insert_query('products_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);
        
        
        //#################################################
        // dodanie rekordow w tablicy products_description_additional
        $zapytanie = "SELECT * FROM products_description_additional WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('products_id',(int)$info["products_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('products_info_description_1',$info["products_info_description_1"]),
                    array('products_info_description_2',$info["products_info_description_2"])
            );
            $db->insert_query('products_description_additional' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);        
        
        
        //#################################################
        // dodanie rekordow w tablicy products_info
        $zapytanie = "SELECT * FROM products_info WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('products_id',(int)$info["products_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('products_info_id',$info["products_info_id"]),
                    array('products_info_name',$info["products_info_name"]),
                    array('products_info_description',$info["products_info_description"]));
            $db->insert_query('products_info' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);        


        //#################################################
        // dodanie rekordow w tablicy products_shipping_time_description
        $zapytanie = "SELECT * FROM products_shipping_time_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('products_shipping_time_id',(int)$info["products_shipping_time_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('products_shipping_time_name',$info["products_shipping_time_name"])
            );
            $db->insert_query('products_shipping_time_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);
        
        
        //#################################################
        // dodanie rekordow w tablicy products_condition_description
        $zapytanie = "SELECT * FROM products_condition_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('products_condition_id',(int)$info["products_condition_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('products_condition_name',$info["products_condition_name"])
            );
            $db->insert_query('products_condition_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);    

        
        //#################################################
        // dodanie rekordow w tablicy products_warranty_description
        $zapytanie = "SELECT * FROM products_warranty_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('products_warranty_id',(int)$info["products_warranty_id"]),
                    array('language_id',$id_dodanej_pozycji),
                    array('products_warranty_name',$info["products_warranty_name"])
            );
            $db->insert_query('products_warranty_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);         


        //#################################################
        // dodanie rekordow w tablicy theme_box_description
        $zapytanie = "SELECT * FROM theme_box_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('box_id',(int)$info["box_id"]),
                    array('box_title',$info["box_title"]),
                    array('language_id',$id_dodanej_pozycji)
            );
            $db->insert_query('theme_box_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy theme_modules_description
        $zapytanie = "SELECT * FROM theme_modules_description WHERE language_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('modul_id',(int)$info["modul_id"]),
                    array('modul_title',$info["modul_title"]),
                    array('language_id',$id_dodanej_pozycji)
            );
            $db->insert_query('theme_modules_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy customers_extra_fields_info
        $zapytanie = "SELECT * FROM customers_extra_fields_info WHERE languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('fields_id',(int)$info["fields_id"]),
                    array('languages_id',$id_dodanej_pozycji),
                    array('fields_name',$info["fields_name"]),
                    array('fields_input_value',$info["fields_input_value"])
            );
            $db->insert_query('customers_extra_fields_info' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


        //#################################################
        // dodanie rekordow w tablicy manufacturers_info
        $zapytanie = "SELECT * FROM manufacturers_info WHERE languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('manufacturers_id',(int)$info["manufacturers_id"]),
                    array('languages_id',$id_dodanej_pozycji),
                    array('manufacturers_url',$info["manufacturers_url"]),
                    array('manufacturers_meta_title_tag',$info["manufacturers_meta_title_tag"]),
                    array('manufacturers_meta_desc_tag',$info["manufacturers_meta_desc_tag"]),
                    array('manufacturers_meta_keywords_tag',$info["manufacturers_meta_keywords_tag"]),
                    array('manufacturers_description',$info["manufacturers_description"])
            );
            $db->insert_query('manufacturers_info' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


         //#################################################
        // dodanie rekordow w tablicy standard_complaints_comments_description
        $zapytanie = "SELECT * FROM standard_complaints_comments_description WHERE languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('comments_id',(int)$info["comments_id"]),
                    array('languages_id',$id_dodanej_pozycji),
                    array('comments_text',$info["comments_text"])
            );
            $db->insert_query('standard_complaints_comments_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);


         //#################################################
        // dodanie rekordow w tablicy standard_order_comments_description
        $zapytanie = "SELECT * FROM standard_order_comments_description WHERE languages_id = '".(int)$_SESSION['domyslny_jezyk']['id']."'";
        $sql = $db->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
            $pola = array(
                    array('comments_id',(int)$info["comments_id"]),
                    array('languages_id',$id_dodanej_pozycji),
                    array('comments_text',$info["comments_text"])
            );
            $db->insert_query('standard_order_comments_description' , $pola);	
            unset($pola);
        }
        $db->close_query($sql);
        unset($info,$zapytanie);

        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('jezyki.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('jezyki.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <?php
          $tabAktualne = array();
          //
          $zapytanie = "select languages_id, name, code from languages";
          $sql = $db->open_query($zapytanie);         
          while ($wynik = $sql->fetch_assoc()) {
              $tabAktualne[] = $wynik['code'];
          }
          $db->close_query($sql);
          unset($wynik,$zapytanie);          
          ?>
          
          <script>
          jQuery.validator.addMethod("notEqual", function(value, element, params) {
            pod = params.split('|');
            wynik = true;
            for (x = 0; x < pod.length; x++) {
                 if ( value == pod[x] ) {
                      wynik = false;
                 }
            }
            return wynik;
          }, "Taka wartość jest już używana."); 
          
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                kod: {
                  required: true,
                  notEqual: "<?php echo implode('|', (array)$tabAktualne); ?>"
                }                  
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane."
                },
                kod: {
                  required: "Pole jest wymagane."
                }                  
              }
            });
          });
          </script> 

          <?php
          unset($tabAktualne);
          ?>          

          <form action="slowniki/jezyki_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <p>
                  <label class="required" for="nazwa">Nazwa:</label>
                  <input type="text" name="nazwa" id="nazwa" value="" size="35" />
                </p>

                <p>
                  <label class="required" for="kod">Kod:</label>
                  <input type="text" name="kod" id="kod" value="" size="5" />
                </p>  

                <p>
                  <label for="foto">Ikona:</label>           
                  <input type="text" name="zdjecie" size="95" value="" class="obrazek" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" /><em class="TipIkona"><b>Kliknij dwukrotnie w pole obok żeby otworzyć okno przeglądarki zdjęć</b></em>
                  <span class="PrzegladarkaZdjec TipChmurka" onclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')"><b>Kliknij żeby otworzyć okno przeglądarki zdjęć</b></span>
                </p>      

                <div id="divfoto" style="padding-left:10px; display:none">
                  <label>Ikona:</label>
                  <span id="fofoto">
                      <span class="zdjecie_tbl">
                          <img src="obrazki/_loader_small.gif" alt="" />
                      </span>
                  </span> 
                </div>                 

                <p>
                  <label for="sort">Kolejność wyświetlania:</label>
                  <input type="text" name="sort" id="sort" value="" size="5" />
                </p>           

                <p>
                  <label for="domyslna_waluta">Domyślna waluta języka:</label>
                  <?php
                  $tablica = array();
                  $zap = "select * from currencies";
                  $sqls = $db->open_query($zap);
                  
                  while ($nazwa = $sqls->fetch_assoc()) {
                      $tablica[] = array('id' => $nazwa['currencies_id'], 'text' => $nazwa['title']);
                  }

                  echo Funkcje::RozwijaneMenu('domyslna_waluta', $tablica, '', 'id="domyslna_waluta"');
                  unset($tablica);
                  ?>                         
                </p>                  
               
                <p>
                  <label>Czy język jest domyślnym:</label>
                  <input type="radio" value="0" name="domyslny" id="domyslny_nie" checked="checked" /><label class="OpisFor" for="domyslny_nie">nie</label>
                  <input type="radio" value="1" name="domyslny" id="domyslny_tak" /><label class="OpisFor" for="domyslny_tak">tak</label>
                </p> 
                
                <p style="padding-top:10px">
                    <span style="color:#ff0000">UWAGA !! Zmiana języka na domyślny lub zamiana domyślnej waluty dla języka będzie wymagała aktualizacji kursów walut w menu Słowniki / Waluty</span>
                </p>  

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('jezyki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}