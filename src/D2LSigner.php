<?php

declare(strict_types=1);

namespace Petersons\D2L;

/** Signer class used to create token signatures for use in Valence API calls. */
final class D2LSigner
{
    /**
     * Produce a hash signature for a base string data, using a key.
     *
     * Both the key and data parameters should be UTF-8 strings.
     *
     * This method first generates a SHA-256-based HMAC of the provided base
     * string data. Then, it renders the result URL-safe by Base64-encoding it,
     * and removing all equal-sign characters, replacing all plus-sign characters
     * with hyphens, and replacing all forward-slash characters with underbars.
     *
     * @param string $key  Key to use for signing.
     * @param string $data Signature base string to sign.
     *
     * @return string Hash signature.
     */
    public static function getBase64HashString(string $key, string $data): string
    {
        $return = hash_hmac('sha256', utf8_encode($data), utf8_encode($key), true);
        $return = base64_encode($return);

        $return = str_replace('=', '', $return);
        $return = str_replace('+', '-', $return);
        $return = str_replace('/', '_', $return);

        return $return;
    }
}
