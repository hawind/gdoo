<?php namespace Gdoo\Chat\Services;

class PushService
{
    private $api;
    private $key;
    private $cert;
    private $caPath;
    private $connectTimeout;
    private $timeout;
    private $useSSL = false;

    public function __construct() {
        $this->api = env('REALTIME_API');
        $this->key = env('REALTIME_KEY');
        $this->api = $this->api.'?'.$this->generateSignature();
    }

    public function generateSignature() {
        $timestamp = time();
        $nonce = rand(10000, 99999);
        $signature = hash_hmac('sha256', $this->key.$timestamp.$nonce, $this->key);
        $query = "signature={$signature}&amp;timestamp={$timestamp}&amp;nonce={$nonce}";
        return $query;
    }

    public function send(array $data) {
        return $this->request($data);
    }

    public function useSSL(bool $value) {
        $this->useSSL = $value;
        return $this;
    }

    public function setCert(string $cert) {
        $this->cert = $cert;
        return $this;
    }

    public function setCAPath(string $caPath) {
        $this->caPath = $caPath;
        return $this;
    }

    public function setConnectTimeout(int $connectTimeout) {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function setTimeout(int $timeout) {
        $this->timeout = $timeout;
        return $this;
    }

    private function request(array $params) {
        $ch = curl_init();
        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }
        if ($this->timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        }
        if ($this->useSSL) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            if ($this->cert) {
                curl_setopt($ch, CURLOPT_CAINFO, $this->cert);
            }
            if ($this->caPath) {
                curl_setopt($ch, CURLOPT_CAPATH, $this->caPath);
            }
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
	    curl_setopt($ch, CURLOPT_USERAGENT, 'realtime/1.0');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($ch, CURLOPT_URL, $this->api);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
        if (empty($headers["http_code"]) || ($headers["http_code"] != 200)) {
            $data = '{"success":false,"code":'.$headers["http_code"].',"msg":"'.$error.'"}';
        }
        $json = json_decode($data, true);
        return $json;
    }

    private function getHeaders() {
        return [
            'Content-Type: application/json',
        ];
    }
}