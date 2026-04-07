<?php

namespace App\Services;

class EncryptionService
{
    public static function decrypt(string $encryptedData): array
    {
        // Use OpenSSL for AES decryption (compatible with crypto-js)
        $data = base64_decode($encryptedData);
        $salt = substr($data, 8, 8);
        $ct = substr($data, 16);

        $rounds = 3;
        $data00 = env('APP_KEY').$salt;
        $hash = [];
        $hash[0] = hash('md5', $data00, true);
        $result = $hash[0];

        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('md5', $hash[$i - 1].$data00, true);
            $result .= $hash[$i];
        }

        $key = substr($result, 0, 32);
        $iv = substr($result, 32, 16);

        $decrypted = openssl_decrypt($ct, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        return json_decode($decrypted, true);
    }

    public static function encrypt(array $data): string
    {
        $jsonString = json_encode($data);

        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';

        while (strlen($salted) < 48) {
            $dx = hash('md5', $dx.env('APP_KEY').$salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);

        $encrypted = openssl_encrypt($jsonString, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode('Salted__'.$salt.$encrypted);
    }
}
