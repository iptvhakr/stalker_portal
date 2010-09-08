<?php

class System
{
    
    
    private static $words = array();
    
    /**
     * Encodes data whith MIME base64 safe for url.
     *
     * @param string $input
     * @return string
     */
    public static function base64_encode($input){
        
        return strtr(base64_encode($input), '+/=', '-_,');
    }
    
    
    /**
     * Decodes data encoded by System::base64_encode().
     *
     * @param string $input
     * @return string
     */
    public static function base64_decode($input){
        
        return base64_decode(strtr($input, '-_,', '+/='));
    }
    
    /**
     * Sets the value of the variable $words.
     *
     * @param array $words_arr
     */
    public static function set_words($words_arr){
        
        self::$words = $words_arr;
    }
    
    /**
     * Returns a string according to the localization.
     *
     * @param string $alias
     * @return string | null
     */
    public static function word($alias){
        
        if (key_exists($alias, self::$words)){
            return self::$words[$alias];
        }
        
        return null;
    }
}

?>