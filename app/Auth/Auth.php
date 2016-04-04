<?php


namespace App\Auth;

use App\Helpers\Cookie;
use App\Models\User;

class Auth
{
    private $config;
    private $session;

    public function __construct($config) {
        $this->config = $config;
        $this->session = $this->config->get('auth.session');
    }

    public function attempt($email, $password) {
        $user = User::where('active', true)
            ->where('email', $email)
            ->first();

        if ( ! $user) {
            return false;
        }

        if (password_verify($password, $user->password)) {
            $_SESSION[$this->session] = $user->id;
            return true;
        }

        return false;
    }

    public function check() {
        return isset($_SESSION[$this->session]);
    }

    public function user() {
        if ($this->check()) {
            return User::find($_SESSION[$this->session]);
        }

        return null;
    }

    public function logout() {
        if ($_COOKIE[$this->config->get('auth.remember')]) {
            $this->user()->removeRememberCredentials();

            Cookie::deleteCookie($this->config->get('auth.remember'));
        }

        unset($_SESSION[$this->session]);
    }

    public function hashPassword($password) {
        return password_hash($password, $this->config->get('app.hash.algo'), [
            'cost' => $this->config->get('app.hash.cost'),
        ]);
    }
}