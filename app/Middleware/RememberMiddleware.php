<?php


namespace App\Middleware;

use App\Helpers\Cookie;
use App\Helpers\Hash;
use App\Models\User;
use Carbon\Carbon;
use Slim\Http\Request;
use Slim\Http\Response;

class RememberMiddleware extends Middleware
{
    public function __invoke(Request $request, Response $response, $next) {
        if (Cookie::getCookie($this->container->config->get('auth.remember')) && $this->container->auth->user() == null) {
            $data = Cookie::getCookie($this->container->config->get('auth.remember'));
            $credentials = explode('___', $data);

            if (empty(trim($data)) || count($credentials) !== 2) {
                $response->withRedirect($this->container->routes->pathFor('home'));
            }
            else {
                $identifier = $credentials[0];
                $token = Hash::hash($credentials[1]);

                $user = User::where('remember_identifier', $identifier)->first();

                if ($user) {
                    if (Hash::hashCheck($token, $user->remember_token)) {
                        $_SESSION[$this->container->config->get('auth.session')] = $user->id;

                        Cookie::setCookie(
                            $this->container->config->get('auth.remember'),
                            "{$identifier}___{$token}",
                            Carbon::parse('+6 month')->timestamp
                        );
                    }
                    else {
                        $user->removeRememberCredentials();
                    }
                }
            }
        }

        $response = $next($request, $response);

        return $response;
    }
}