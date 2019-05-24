<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $authMiddleware = function($request, $response, $next) {
        if(!empty($_SESSION['user_id'])) {
            return $next($request, $response);
        } else {
            return $response->withHeader('Location', $this->router->pathFor('show.login'));
        }
    };

    $app->group('/user', function() use($app, $authMiddleware) {
        $app->get('/logout', \SlimApp\Classes\User::class . ':logout')->setName('logout')
            ->add($authMiddleware);
        $app->get('/signup', \SlimApp\Classes\User::class . ':showRegisterPage')->setName('show.register');
        $app->post('/signup', \SlimApp\Classes\User::class . ':register')->setName('register');
        $app->get('/login', \SlimApp\Classes\User::class . ':showLoginPage')->setName('show.login');
        $app->post('/login', \SlimApp\Classes\User::class . ':login')->setName('login');
    });

    $app->get('/', \SlimApp\Classes\Home::class . ':index')->setName('home');
    $app->get('/about', \SlimApp\Classes\Home::class . ':aboutUs')->setName('about');
    $app->get('/dashboard', \SlimApp\Classes\Home::class . ':dashboard')->setName('show.dashboard')
        ->add($authMiddleware);

};
