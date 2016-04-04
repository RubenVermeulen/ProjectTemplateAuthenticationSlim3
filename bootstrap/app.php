<?php

use Respect\Validation\Validator as v;

/*
|--------------------------------------------------------------------------
| Timezone
|--------------------------------------------------------------------------
|
| Set the default timezone.
|
*/

date_default_timezone_set('Europe/Brussels');

/*
|--------------------------------------------------------------------------
| Time language
|--------------------------------------------------------------------------
|
| Set the time language.
|
*/

setlocale(LC_TIME, 'nl_NL');

/*
|--------------------------------------------------------------------------
| Sessions
|--------------------------------------------------------------------------
|
| Start sessions.
|
*/

session_start();

/*
|--------------------------------------------------------------------------
| Error reporting
|--------------------------------------------------------------------------
|
| Show error reports or not.
|
*/

ini_set('display_errors', 'On');

/*
|--------------------------------------------------------------------------
| Dependencies
|--------------------------------------------------------------------------
|
| Autoload the dependencies from the vendor folder.
|
*/

require __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Slim instance
|--------------------------------------------------------------------------
|
| Create a new slim instance and define the current application mode.
|
|
*/

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'mode' => file_get_contents(__DIR__ . '/../mode.php'),
    ],
]);

$container = $app->getContainer();

/*
|--------------------------------------------------------------------------
| Load config
|--------------------------------------------------------------------------
|
| Load config into Slim.
|
*/

$container['config'] = function($container) {
    return \Noodlehaus\Config::load(__DIR__ . '/../config/' . trim($container->settings->get('mode')) . '.php');
};

/*
|--------------------------------------------------------------------------
| Eloquent
|--------------------------------------------------------------------------
|
| Boot up Eloquent for further use.
| New models will be able to extend Eloquent.
|
*/

$capsule = new Illuminate\Database\Capsule\Manager();
$capsule->addConnection($container->config->get('db'));
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function($container) use ($capsule) {
    return $capsule;
};

$container['auth'] = function($container) {
    return new \App\Auth\Auth($container->config);
};

$container['view'] = function($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user(),
    ]);

    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

$container['notFoundHandler'] = function($container) {
    return function($request, $response) use($container) {
        $container->view->render($response, 'errors/404.twig');

        return $response->withStatus(200);
    };
};

$container['validator'] = function($container) {
    return new \App\Validation\Validator();
};

$container['randomlib'] = function($container) {
    $factory = new RandomLib\Factory();

    return $factory->getMediumStrengthGenerator();
};

$container['csrf'] = function($container) {
    return new \Slim\Csrf\Guard();
};

$container['flash'] = function($container) {
    return new \Slim\Flash\Messages();
};

$container['mailer'] = function($container) {
    return new \App\Mail\MailerMailgun(
        new \Mailgun\Mailgun($container->config->get('mailgun.private_key')),
        $container->config,
        $container->view
    );
};

/*
|--------------------------------------------------------------------------
| Container Controllers
|--------------------------------------------------------------------------
|
| Require in all containers for the controllers.
|
*/

require __DIR__ . '/containers/controllers.php';

/*
|--------------------------------------------------------------------------
| Middleware
|--------------------------------------------------------------------------
|
| Load all middleware.
|
*/

$app->add(new \App\Middleware\RememberMiddleware($container));
$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

$app->add($container->csrf);

/*
|--------------------------------------------------------------------------
| Validation Rules
|--------------------------------------------------------------------------
|
| Define path to the validation rules for Respect/Validation.
|
*/

v::with('App\\Validation\\Rules\\');

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
|
| Include all routes.
|
*/

require __DIR__ . '/../app/routes.php';