<?php


namespace App\Helpers;


class Cookie
{
    public static function setCookie($name, $value, $expire, $path = '/') {
        setcookie($name, $value, $expire, $path);
    }

    public static function deleteCookie($name, $path = '/') {
        setcookie($name, '', 1, $path);
    }

    public static function getCookie($name) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }
}