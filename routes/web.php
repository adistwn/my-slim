<?php

/**
 * Routes 
 * 
 * @author Adistwn
 */

namespace Routes;

use App\User;
use \Valitron\Validator;
use \Lcobucci\JWT\Parser;
use \Lcobucci\JWT\Builder;
use \Lcobucci\JWT\Signer\Key;
use \Lcobucci\JWT\ValidationData;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class Web
{

    /**
     * Routes
     * 
     * @return Response $response
     */
    public function routes()
    {
        $config = [
            'settings' => [
                'displayErrorDetails' => true,
            ],
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

                        $jwt = $this->generateToken($user->id);
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

    /**
     * Generate token JWT
     *
     * @package \Lcobucci\JWT\
     * @param Int $uid
     * @return mixed $token
     */
    public function generateToken(Int $uid)
    {
        $time = time();
        $token = (new Builder())->issuedBy('localhost/my-slim') // Configures the issuer (iss claim)
            ->permittedFor('localhost') // Configures the audience (aud claim)
            ->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($time) // Configures the time that the token can be used (nbf claim)
            ->expiresAt($time + 86400) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', $uid) // Configures a new claim, called "uid"
            ->getToken(); // Retrieves the generated token

        $token->getHeaders(); // Retrieves the token headers
        $token->getClaims(); // Retrieves the token claims
        return $token;
    }

    /**
     * Validating token JWT
     *
     * @param String $token
     * @return bool
     */
    public function validateToken(String $token)
    {
        $validToken = (new Parser())->parse((string) $token);

        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer('localhost/my-slim');
        $data->setAudience('localhost');
        $data->setId('4f1g23a12aa');

        if ($validToken->validate($data) === false) {
            return false;
        }

        return true;
    }
}
