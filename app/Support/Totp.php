<?php namespace App\Support;

/**
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use App\Support\Base32;

class Totp
{
    protected $passCodeLength;
    protected $secretLength;
    protected $pinModulo;

    /**
     * @param int $passCodeLength
     * @param int $secretLength
     */
    public function __construct($passCodeLength = 6, $secretLength = 10)
    {
        $this->passCodeLength = $passCodeLength;
        $this->secretLength   = $secretLength;
    }

    /**
     * get TimeStamp
     * period.
     * @return integer
     **/
    public function getTimeStamp()
    {
        return floor(time() / 30);
    }

    /**
     * @param $secret
     * @param $code
     * @return bool
     */
    public function generateByTime($secret, $code)
    {
        for ($i = -1; $i <= 1; $i++) {
            if ($this->generateByCounter($secret, $this->getTimeStamp() + $i) == $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $secret
     * @param null $time
     * @return string
     */
    public function generateByCounter($secret, $time = null)
    {
        if ($time === null) {
            $time = $this->getTimeStamp();
        }

        $secret = Base32::decode($secret);

        $time = pack("N", $time);
        $time = str_pad($time, 8, chr(0), STR_PAD_LEFT);

        $hash = hash_hmac('sha1', $time, $secret, true);
        $offset = ord(substr($hash, -1));
        $offset = $offset & 0xF;

        $truncatedHash = self::hashToInt($hash, $offset) & 0x7FFFFFFF;
        $pinValue = str_pad($truncatedHash % pow(10, $this->passCodeLength), $this->passCodeLength, "0", STR_PAD_LEFT);

        return $pinValue;
    }

    /**
     * @param $bytes
     * @param $start
     * @return integer
     */
    protected static function hashToInt($bytes, $start)
    {
        $input = substr($bytes, $start, strlen($bytes) - $start);
        $val2 = unpack("N", substr($input, 0, 4));

        return $val2[1];
    }

    /**
     * @return string
     */
    public function generateSecret($secret = null)
    {
        if ($secret === null) {
            $secret = '';
            for ($i = 1; $i <= $this->secretLength; $i++) {
                $c = rand(0, 255);
                $secret .= pack("c", $c);
            }
        }
        return Base32::encode($secret);
    }

    /**
     * @param string $user
     * @param string $host
     * @param string $secret
     * @param string $issuer
     * @return string
     */
    public static function getURL($user, $host, $secret, $issuer)
    {
        $encoderURL = sprintf("otpauth://totp/%s@%s%%3Fsecret%%3D%s%%3Fissuer%%3D%s", $user, $host, $secret, urlencode($issuer));
        return $encoderURL;
    }
}

/*
$secret = '2222222222222222';
$code = "181419";

$g = new TimeAuthenticator();

print "Current Code is: ";
print $g->generateByCounter($secret);

print "\n";

print "Check if $code is valid: ";

if ($g->generateByTime($secret, $code))
{
    print "YES \n";
}
else
{
    print "NO \n";
}

$secret = $g->generateSecret();
print "Get a new Secret: $secret \n";

print "The QR Code for this secret (to scan with the Google Authenticator App: \n";
print $g->getURL('fvzone','gmail.com', $secret);
print "\n";
*/
