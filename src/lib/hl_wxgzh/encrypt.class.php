<?php
class Encrypt{

    static function raw($sign_str){
        if($key = openssl_pkey_get_private(self::privateKey)){
            openssl_sign($sign_str,$sign,$key);
            openssl_free_key($key);
            return base64_encode($sign);
        }
        return false;
    }

    static function verify($sign_str,$sign){
        if($key = openssl_pkey_get_public(self::publicKey)){
            $result=openssl_verify($sign_str,base64_decode($sign),$key);
            openssl_free_key($key);
            return $result;
        }
        return false;
    }
}
?>
