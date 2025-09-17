<?php
class oiTracker
{
    private $key = '';
    function __construct($key)
    {
        $this->key = $key;
    }
    
    function eOrder($data)
    {
        $data['key'] = $this->key; 
        $value = base64_encode(json_encode($data));
        $url = 'http://tracker.okazje.info.pl/'.$value.'/eOrder.json?'.rand(0, 1000000);
        $return = json_decode(file_get_contents($url));
        return (isset($return->return ) && $return->return === true);
    }

}
?>
