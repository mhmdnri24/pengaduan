<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('uuid_v4')) {
    /**
     * Generate a version 4 (random) UUID.
     *
     * @return string The UUID.
     */
    function uuid_v4() {
        // Generate 16 bytes (128 bits) of random data.
        $data = random_bytes(16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('generate_uuid')) {
    /**
     * Alias untuk uuid_v4() untuk konsistensi penamaan
     *
     * @return string The UUID.
     */
    function generate_uuid() {
        return uuid_v4();
    }
}