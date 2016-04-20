<?php

use Stalker\Lib\Core\Config;

/**
 * Class XtreamCodes
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */
class XtreamCodes {

    public static function getHash($mac_add, $ip, $channel_id, $max_seconds) {
        $encrypt = serialize($mac_add . '=' . $ip . '=' . $channel_id . '=' . (time() + $max_seconds));
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $key = pack('H*', md5(Config::get('xtream_key')));
        $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt . $mac, MCRYPT_MODE_CBC, $iv);
        $encoded = urlencode(base64_encode(base64_encode($passcrypt) . '|' . base64_encode($iv)));

        return $encoded;

    }

}