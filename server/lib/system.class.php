<?php

class System
{
    
    /**
     * Encodes data whith MIME base64 safe for url.
     *
     * @static
     * @param string $input
     * @return string
     */
    public static function base64_encode($input){
        
        return strtr(base64_encode($input), '+/=', '-_,');
    }
    
    
    /**
     * Decodes data encoded by System::base64_encode().
     *
     * @static
     * @param string $input
     * @return string
     */
    public static function base64_decode($input){
        
        return base64_decode(strtr($input, '-_,', '+/='));
    }
    
    /**
     * Returns all words.
     *
     * @static
     * @return array $words
     */
    public static function get_all_words(){

        require_once PROJECT_PATH."/lang/stb.php";

        return $words;
    }

    /**
     * Convert seconds to human readable length.
     *
     * echo System::convertTimeLengthToHuman(100);
     * > 1:40
     *
     * echo System::convertTimeLengthToHuman(500);
     * > 8:20
     *
     * @static
     * @param int $length seconds
     * @return string
     */
    public static function convertTimeLengthToHuman($length){

        $hh = floor($length / 3600);

        $mm = floor(($length - $hh*3600)/60);

        $ss = $length - $hh*3600 - $mm*60;

        $result = '';

        if ($hh > 0){
            /// Hours
            $result .= $hh._('h').' ';
        }

        if ($mm > 0){
            /// Minutes
            $result .= $mm._('m').' ';
        }

        if ($ss > 0){
            /// Seconds
            $result .= $ss._('s').' ';
        }

        return $result;
    }

    /**
     * Convert datetime to human readable date.
     *
     * @static
     * @param int|string timestamp $timestamp
     * @return string
     */
    public static function convertDatetimeToHuman($timestamp){

        $this_mm = date("m");
        $this_dd = date("d");
        $this_yy = date("Y");

        if ($timestamp > mktime(0,0,0, $this_mm, $this_dd, $this_yy) && $timestamp < mktime(24,0,0, $this_mm, $this_dd, $this_yy)){
            $human_date = _('today').', '.date("H:i", $timestamp);
        }elseif ($timestamp > mktime(0,0,0, $this_mm, $this_dd-1, $this_yy) && $timestamp < mktime(24,0,0, $this_mm, $this_dd-1, $this_yy)){
            $human_date = _('yesterday').', '.date("H:i", $timestamp);
        }else{
            $human_date = date("d.m.Y H:i", $timestamp);
        }

        return $human_date;
    }
}

?>