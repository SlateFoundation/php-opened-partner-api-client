<?php

namespace OpenEd;


class SignedServerRequest
{
    private $client_id;
    private $client_secret;
    private $nces_id;

    public function __construct($client_id, $client_secret, $nces_id = null)
    {
        $this->client_secret = $client_secret;
        $this->client_id = $client_id;
        $this->nces_id = $nces_id;
    }

    private static function base64UrlEncode($input)
    {
        return str_replace('+', '-', str_replace('/', '_', preg_replace('/=+$/', '', base64_encode($input))));
    }

    private static function generateToken($username)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes(64));
        } elseif (function_exists('random_bytes')) {
            return bin2hex(random_bytes(64));
        } else {
            return sha1($username);
        }
    }

    public function generateSignedRequest($username, $params = [], $token = null)
    {
        $envelope = [
            'username' => $username,
            'client_id' => $this->client_id,
            'token' => $token ?: self::generateToken($username),
            'algorithm' => 'HMAC-SHA256'
        ];

        $envelope = array_merge($params, $envelope);

        if ($this->nces_id != null && !isset($envelope['school_nces_id'])) {
            $envelope['school_nces_id'] = (string) $this->nces_id;
        }

        $json_envelope = json_encode($envelope);

        print "Envelope contents: \n" . $json_envelope . "\n";

        $envelope = self::base64UrlEncode($json_envelope);
        $signature = self::base64UrlEncode(hash_hmac('SHA256', $envelope, $this->client_secret));

        return "$signature.$envelope";
    }

    public function setNcesId($nces_id) {
        $this->nces_id = (string) $nces_id;
    }

    public function getNcesId() {
        return $this->nces_id;
    }
}
