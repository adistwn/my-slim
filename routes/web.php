<?php

/**
 * Routes 
 * 
 * @author Adistwn
 */

namespace Routes;

use App\Users;
use Valitron\Validator;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class Web
{
    public function routes()
    {
        $config = [
            'displayErrorDetails' => true,
        ];
        $app = new \Slim\App($config);

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

            $v = new Validator($input);
            $v->rule('required', 'name');

            if ($v->validate()) {
                
                $result = [
                    'error'   => false,
                    'message' => 'Created',
                    'data'    => []
                ];

            } else {

                $result = [
                    'error'   => false,
                    'message' => $v->errors(),
                    'data'    => []
                ];
            }
            return $response->withJson($result);
        });
        

        return $app;
    }
}
