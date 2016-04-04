<?php


namespace App\Helpers;


class Hash
{
    public static function hash($input) {
        return hash('sha256', $input);
    }

    public static function hashCheck($known, $user) {
        if (function_exists('hash_equals')) {
            return hash_equals($known, $user);
        }
        else {
            return $known === $user;
        }
    }
}