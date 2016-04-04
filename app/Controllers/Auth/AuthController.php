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
            'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable()->length(0, 255),
            'name' => v::notEmpty()->alpha()->length(0, 255),
            'password' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $identifier = $this->randomlib->generateString(128);

        $user = User::create([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
            'password' => $this->auth->hashPassword($request->getParam('password')),
            'active' => false,
            'active_hash' => Hash::hash($identifier),
        ]);

        $user->permissions()->create([]);

        $this->mailer->sendMessage(
            'emails/auth/registered.twig',
            [
                'user' => $user,
                'identifier' => $identifier,
            ],
            [
                'from' => $this->config->get('address.noreply'),
                'to' => $user->email,
                'subject' => 'Registered',
            ]
        );

        $this->flash->addMessage('success', 'You have been signed up, please check your mailbox for an activation email!');

        return $response->withRedirect($this->router->pathFor('auth.signin'));

    }

    public function getSignOut(Request $request, Response $response) {
        $this->auth->logout();

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getActivate(Request $request, Response $response) {
        $email = $request->getParam('email');
        $hashedIdentifier = Hash::hash($request->getParam('identifier'));

        $user = User::where('email', $email)
            ->where('active', false)
            ->first();

        if ( ! $user || ! Hash::hashCheck($hashedIdentifier, $user->active_hash)) {
            $this->flash->addMessage('error', 'It was not possible to activate your account!');
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        $user->activateAccount();

        $this->flash->addMessage('success', 'Your account has been activated, you can now log in!');
        return $response->withRedirect($this->router->pathFor('auth.signin'));
    }
}