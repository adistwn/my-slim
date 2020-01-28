<?php

/**
 * Handle JWT
 * 
 * @author AdiStwn
 */

namespace App\Controllers;

use \Lcobucci\JWT\Parser;
use \Lcobucci\JWT\Builder;
use \Lcobucci\JWT\Signer\Key;
use \Lcobucci\JWT\ValidationData;
use \Lcobucci\JWT\Signer\Hmac\Sha256;

class Jwt
{
    /**
     * Generate token JWT
     *
     * @package \Lcobucci\JWT\
     * @param Int $uid
     * @return mixed $token
     */
    public function generateToken(Int $uid)
    {
        $time  = time();
        $signer = new Sha256();

        $token = (new Builder())
            ->issuedBy('localhost/my-slim') // Configures the issuer (iss claim)
            ->permittedFor('localhost') // Configures the audience (aud claim)
            ->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($time) // Configures the time that the token can be used (nbf claim)
            ->expiresAt($time + 86400) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', $uid) // Configures a new claim, called "uid"
            ->getToken($signer, new Key('example-key')); // Retrieves the generated token

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
        $signer = new Sha256();
        $validToken = (new Parser())->parse((string) $token);

        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer('localhost/my-slim');
        $data->setAudience('localhost');
        $data->setId('4f1g23a12aa');

        if ($validToken->validate($data) === false || $validToken->verify($signer, 'example-key') === false) {
            return false;
        }

        return true;
    }
}
