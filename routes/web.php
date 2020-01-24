<?php

/**
 * Routes 
 * 
 * @author Adistwn
 */

namespace Routes;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Users;

class Web
{
    public function routes()
    {
        $app = new \Slim\App();

        /**
         * Default route
         */
        $app->get('/', function (Request $request, Response $response) {
            $result = Users::all();
            return $response->withJson($result, 200, JSON_PRETTY_PRINT);
        });

        /**
         * Create user
         */
        $app->post('/store', function (Request $request, Response $response) {
            
            $input = $request->getParsedBody();
            return $response->write($input['name']);
        });
        

        return $app;
    }
}
