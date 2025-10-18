<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Aqui, você define os domínios que serão permitidos para autenticação com 
    | cookies. Para o ambiente de desenvolvimento local, inclua localhost e 
    | qualquer outra porta que esteja usando no frontend e backend.
    |
    */
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1', // Adicione aqui os domínios
        Sanctum::currentApplicationUrlWithPort() // Para pegar o domínio atual do aplicativo
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | O guard utilizado pelo Sanctum para autenticação. O valor 'web' funciona 
    | para uma SPA que usa cookies.
    |
    */
    'guard' => ['web'], // Isso deve estar configurado corretamente para uso de cookies em uma SPA

    /*
    |--------------------------------------------------------------------------
    | Expiração dos Tokens
    |--------------------------------------------------------------------------
    |
    | Aqui você pode definir o tempo de expiração dos tokens de acesso. 
    | O valor null significa que o token nunca irá expirar. Se você quiser 
    | tokens que expiram, defina o valor em minutos (ex: 60 para 1 hora).
    |
    */
    'expiration' => null, // Você pode definir a expiração para tokens, se necessário. Use um valor como 60 para 1 hora, por exemplo

    /*
    |--------------------------------------------------------------------------
    | Prefixo do Token
    |--------------------------------------------------------------------------
    |
    | Um prefixo opcional para os tokens gerados. Normalmente é deixado vazio, 
    | mas você pode configurar caso queira diferenciar os tokens de diferentes
    | aplicações ou propósitos.
    |
    */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''), // Deixe vazio ou defina um prefixo como 'api'

    /*
    |--------------------------------------------------------------------------
    | Middleware do Sanctum
    |--------------------------------------------------------------------------
    |
    | Middleware padrão utilizado pelo Sanctum para autenticação em SPA. 
    | Você pode deixar essa configuração como está, a menos que tenha 
    | necessidades específicas de personalização.
    |
    */
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
