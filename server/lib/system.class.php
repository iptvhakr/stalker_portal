<?php

class System
{
    
    
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
     * Decodes data encoded by System::base64_encode() .
     *
     * @param string $input
     * @return string
     */
    public static function base64_decode($input){
        
        return base64_decode(strtr($input, '-_,', '+/='));
    }
}

?>