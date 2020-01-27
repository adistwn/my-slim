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
use \Firebase\JWT\JWT;



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

            $validate = new Validator($input);
            $validate->rule('required', ['name','username','password','email']);
            $validate->rule('email', 'email');

            if ($validate->validate()) {

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
                    'error'   => true,
                    'message' => $validate->errors(),
                    'data'    => []
                ];
            }
            return $response->withJson($result, 200, JSON_PRETTY_PRINT);
        });
        
        /**
         * Check login
         * 
         * @param string username
         * @param string password
         * 
         * @return json
         */
        $app->post('/auth', function (Request $request, Response $response) {

            $input = $request->getParsedBody();

            $validate = new Validator($input);
            $validate->rule('required', ['username', 'password']);
            
            if ($validate->validate()) {

                $user = User::where('username', $input['username'])->first();
                if ($user) {

                    if (password_verify($input['password'], $user->password)) {

                        $key = "your_secret";
                        $payload = array(
                            "iss" => "localhost",
                            "aud" => "localhost",
                            "iat" => 1356999524,
                            "nbf" => 1357000000
                        );
                        $jwt = JWT::encode($payload, $key);
                        $user->token = $jwt;
                        $user->save();

                        $result = [
                            'error'   => false,
                            'message' => 'You have successfully logged in',
                            'data'    => [
                                'token' => $jwt,
                            ]
                        ];

                    } else {

                        $result = [
                            'error'   => true,
                            'message' => 'Invalid username or password',
                            'data'    => []
                        ];
                    }

                } else {

                    $result = [
                        'error'   => true,
                        'message' => 'Invalid username or password',
                        'data'    => []
                    ];

                }


            } else {

                $result = [
                    'error'   => true,
                    'message' => $validate->errors(),
                    'data'    => []
                ];
            }
            return $response->withJson($result, 200, JSON_PRETTY_PRINT);

        });

        return $app;
    }
}
