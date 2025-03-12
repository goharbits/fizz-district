<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DecryptRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    private $encryptionKey = '@@fizzdistrict@EncryptionKey!786'; // 32-byte encryption key
    private $iv = '0000000000000000'; // 16-byte IV
    /**
     * Decrypt the AES-256 encrypted data
     *
     * @param string $encryptedData Base64-encoded AES-256 encrypted data
     * @return string Decrypted plain text
     */
    private function decryptData($encryptedData)
    {
        // Decode the base64-encoded encrypted string
        $combined = base64_decode($encryptedData);
        $iv = substr($combined, 0, 16);
        $ciphertext = substr($combined, 16);

        // Decrypt the data using openssl_decrypt
        $decryptedData = openssl_decrypt(
            $ciphertext,          // Ciphertext
            'aes-256-cbc',        // Encryption algorithm (AES-256-CBC)
            $this->encryptionKey, // Encryption key
            OPENSSL_RAW_DATA,     // Pass the data as raw (not base64)
            $iv                   // IV extracted from the combined data
        );

        return $decryptedData ?: 'Decryption failed';
    }

    /**
     * Decrypt each element of the array
     *
     * @param array $encryptedArray Array of encrypted values
     * @return array Array of decrypted values
     */
    private function decryptArray(array $encryptedArray)
    {
        $decryptedArray = [];

        foreach ($encryptedArray as $key => $value) {
            // Decrypt each value in the array and store it in the decrypted array
            $decryptedArray[$key] = $this->decryptData($value);
        }

        return $decryptedArray; // Return the array of decrypted values
    }

    public function handle(Request $request, Closure $next): Response
    {

        $fieldsToDecrypt = ['CreditCardNumber', 'ExpMonth', 'ExpYear', 'CVV','Last4','First6','AddressZip'];
        foreach ($fieldsToDecrypt as $field) {
            if ($request->has($field)) {
                $encryptedValue = $request->input($field);
                $decryptedValue = $this->decryptData($encryptedValue);
                // Replace the encrypted value with the decrypted one
                $request->merge([$field => $decryptedValue]);
            }
        }
        return $next($request);
    }
}