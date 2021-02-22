<?php namespace App\Support;

class Base32
{
    public static $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Test if an encoded string is compatible with this encoder/decoder
     *
     * @param string $data The encoded string.
     * @return boolean Returns true if the encoded string is compatible, otherwise false.
     */
    public static function isValid($data)
    {
        return ((strlen($data) % 8) === 0 && preg_match("/^[".Base32::$charset."]+=*$/i", $data) === 1);
    }

    /**
     * Encode a string of raw data
     *
     * @param string $data String of raw data to encode.
     * @return string Returns the encoded string.
     */
    public static function encode($data)
    {
        $encoded = null;

        if ($data) {
            $binString = '';
            // 'AB' => 01000001 01000010
            foreach (str_split($data) as $char) {
                $binString .= str_pad(decbin(ord($char)), 8, 0, STR_PAD_LEFT);
            }

            // 01000001 01000010 => 01000 00101 00001 00000 => 'IFBA'
            for ($offset = 0; $offset < strlen($binString); $offset += 5) {
                $chunk = str_pad(substr($binString, $offset, 5), 5, 0, STR_PAD_RIGHT);
                $encoded .= Base32::$charset[bindec($chunk)];
            }

            // 'IFBA' => 'IFBA===='
            if (strlen($encoded) % 8) {
                $encoded .= str_repeat('=', 8 - (strlen($encoded) % 8));
            }
        }

        return $encoded;
    }

    /**
     * Decode an encoded string
     *
     * @param string $data String of encoded data to decode.
     * @return string Returns the decoded string.
     * @throws given string is not valid for this encoder/decoder.
     */
    public static function decode($data)
    {
        $decoded = null;

        if ($data) {
            if (!Base32::isValid($data)) {
                throw new \Exception('Invalid base32 string');
            }

            // 'ifba====' => 'IFBA'
            $data = rtrim(strtoupper($data), '=');

            $binString = '';
            // 'IFBA' => 01000 00101 00001 00000
            foreach (str_split($data) as $char) {
                $binString .= str_pad(decbin(strpos(Base32::$charset, $char)), 5, 0, STR_PAD_LEFT);
            }

            // 01000 00101 00001 00000 => 01000001 01000010
            // Assuming it's safe to drop the trailing bits, as if this is a
            // valid Base32 string, they'll be padding zeros anyway.
            $binString = substr($binString, 0, (floor(strlen($binString) / 8) * 8));

            // 01000001 01000010 => 'AB'
            for ($offset = 0; $offset < strlen($binString); $offset += 8) {
                $chunk = str_pad(substr($binString, $offset, 8), 8, 0, STR_PAD_RIGHT);
                $decoded .= chr(bindec($chunk));
            }
        }
        return $decoded;
    }
}
