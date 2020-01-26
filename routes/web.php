<?php

/**
 * Routes 
 * 
 * @author Adistwn
 */

namespace Routes;

use App\User;
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
            $result = User::all();
            return $response->withJson($result, 200, JSON_PRETTY_PRINT);
        });

        /**
         * Create new user
         * 
         * @param string name
         * @param string username
         * @param string password
         * @param string email
         * 
         * @return json
         */
        $app->post('/store', function (Request $request, Response $response) {
            
            $input = $request->getParsedBody();

            $v = new Validator($input);
            $v->rule('required', ['name','username','password','email']);
            $v->rule('email', 'email');

            if ($v->validate()) {

                $exist_email = User::where('email',$input['email'])->first();
                if ($exist_email) {

                    $result = [
                        'error'   => true,
                        'message' => 'Email already exist',
                        'data'    => []
                    ];

                } else {

                    $user             = new User();
                    $user->name       = $input['name'];
                    $user->username   = $input['username'];
                    $user->password   = password_hash($input['password'],PASSWORD_DEFAULT);
                    $user->email      = $input['email'];
                    $user->created_at = date('Y-m-d H:i:s');
                    $user->updated_at = date('Y-m-d H:i:s');

                    if ($user->save()) {
                        
                        $result = [
                            'error'   => false,
                            'message' => 'New user created',
                            'data'    => [
                                'name'     => $input['name'],
                                'username' => $input['username'],
                            ]
                        ];

                    } else {

                        $result = [
                            'error'   => true,
                            'message' => 'Error when create new user',
                            'data'    => []
                        ];
                    }
                }

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
