<?php namespace App\Support;

/**
 * openssl AES加密解密
 *
 * @author Hawind <hawind@qq.com>
 *
 * @version 1.0
 */
class AES
{
    /**
     * openssl aes 加密
     */
    public static function crypto_encrypt($data, $key, $options = OPENSSL_RAW_DATA)
    {
        $iv = substr($key, 0, -16);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, $options, $iv);
        return $encrypted;
    }

    /**
     * openssl aes 解密
     */
    public static function crypto_decrypt($data, $key, $options = OPENSSL_RAW_DATA)
    {
        $iv = substr($key, 0, -16);
        return openssl_decrypt($data, 'aes-256-cbc', $key, $options, $iv);
    }

    /**
     * 加密.
     *
     * @param mixed  $contents   要加密的内容
     * @param string $encryptKey 加密的Key，长度为14，24，32
     *
     * @return string 已加密的内容
     */
    public static function encrypt($data, $key)
    {
        $iv   = openssl_random_pseudo_bytes(16);
        $encrypted = [
            base64_encode($iv),
            openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv)
        ];
        return base64_encode(json_encode($encrypted));
    }

    /**
     * 解密.
     *
     * @param string $data 已加密的内容
     * @param string $key  解密Key
     *
     * @return string 已解密的内容
     */
    public static function decrypt($data, $key)
    {
        $encrypt   = json_decode(base64_decode($data), true);
        $iv        = base64_decode($encrypt[0]);
        $decrypted = openssl_decrypt($encrypt[1], 'aes-256-cbc', $key, 0, $iv);
        return $decrypted;
    }
}
