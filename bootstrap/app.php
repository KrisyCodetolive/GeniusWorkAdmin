<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'entreprise' => \App\Http\Middleware\HasEntreprise::class,
        ]);
        // Le portail des dépenses a son propre accès (département + nom) ; le reste de l'app garde /login.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('depense', 'depense/*', 'depenses/*')
            ? route('employe.depenses.acces')
            : route('login'));
        $middleware->appendToGroup('web', \App\Http\Middleware\RestreindreSessionDepartement::class);
        // Trust les proxies de Coolify (Traefik) pour HTTPS
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PREFIX | Request::HEADER_X_FORWARDED_AWS_ELB);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
