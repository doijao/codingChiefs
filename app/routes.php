<?php
declare(strict_types=1);

use App\Application\Services\ProductServices;
use App\Application\Services\UserServices;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

    session_start();

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });


    $app->get('/api/list/{filter}', function (Request $request, Response $response, $args) use ($app){        
        
        /** Start session */
        session_destroy();        
        $list = new ProductServices($app, $args);
        $result = $list->getResult(); 

        /**
         * Set CSRF Token
         */
        $csrf = $this->get('csrf');
        $nameKey = $csrf->getTokenNameKey();
        $valueKey = $csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);
        
        $result['token'] = [
            $nameKey => $name,
            $valueKey => $value
        ];
        
        $response->getBody()->write(json_encode($result));

        return $response->withHeader("Content-Type", "application/json");
    })->add('csrf');

    
    /**
     * Login and generate Token
     * email: doi.jao@gmail.com
     * password: Password@123
     */    
    $app->post('/api/auth', function (Request $request, Response $response) use ($app){
       
        $param = $request->getParsedBody();        
        $result = new UserServices($app, $param);
        $response->getBody()->write(json_encode($result->getResult()));

        return $response->withHeader("Content-Type", "application/json");
    });

};
