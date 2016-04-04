<?php


namespace App\Controllers\Auth;

use App\Helpers\Cookie;
use Carbon\Carbon;
use Slim\Http\Request;
use Slim\Http\Response;

use App\Models\User;
use App\Helpers\Hash;
use App\Controllers\Controller;

use Respect\Validation\Validator as v;


class AuthController extends Controller
{
    public function getSignIn(Request $request, Response $response) {
        return $this->view->render($response, 'auth/signin.twig');
    }

    public function postSignIn(Request $request, Response $response) {
        $validation = $this->validator->validate($request, [
            'email' => v::notEmpty()->email(),
            'password' => v::notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        $auth = $this->auth->attempt(
            $request->getParam('email'),
            $request->getParam('password')
        );

        if ( ! $auth) {
            $this->flash->addMessage('error', 'Could not sign you in with those details!');

            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        if ($request->getParam('remember') === 'on') {
            $user = $this->auth->user();

            $rememberIdentifier = $user->remember_identifier;
            $rememberToken = $user->remember_token;

            if ( ! isset($rememberIdentifier) && ! isset($rememberToken)) {
                $rememberIdentifier = $this->randomlib->generateString(128);
                $rememberToken = $this->randomlib->generateString(128);

                $user->updateRememberCredentials(
                    $rememberIdentifier,
                    Hash::hash($rememberToken)
                );
            }

            Cookie::setCookie(
                $this->config->get('auth.remember'),
                "{$rememberIdentifier}___{$rememberToken}",
                Carbon::parse('+6 month')->timestamp
            );
        }

        $this->flash->addMessage('success', 'You have been signed in!');

        return $response->withRedirect($this->router->pathFor('home'));
    }


    public function getSignUp(Request $request, Response $response) {
        return $this->view->render($response, 'auth/signup.twig');
    }

    public function postSignUp(Request $request, Response $response) {
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
            'name' => v::notEmpty()->alpha(),
            'password' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }


        $user = User::create([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
            'password' => $this->auth->hashPassword($request->getParam('password')),
        ]);

        $user->permissions()->create([]);

        $this->flash->addMessage('success', 'You have been signed up!');

        $this->auth->attempt($user->email, $request->getParam('password'));

        return $response->withRedirect($this->router->pathFor('home'));

    }

    public function getSignOut(Request $request, Response $response) {
        $this->auth->logout();

        return $response->withRedirect($this->router->pathFor('home'));
    }
}