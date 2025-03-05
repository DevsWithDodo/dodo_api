<?php

namespace App\Services;

use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\Http;
use Lcobucci\JWT\ClaimsFormatter;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\CannotEncodeContent;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\InvalidKeyProvided;
use Lcobucci\JWT\Signer\CannotSignPayload;
use Lcobucci\JWT\Signer\Ecdsa\ConversionFailed;
use Lcobucci\JWT\Token\Builder as TokenBuilder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;

class AppleAuthenticationService
{
    /**
     * Parse the client ID based on the client type.
     * @param string $clientType  The type of client. Can be 'web', 'ios' or 'android'.
     * @return string The client ID to be used in the JWT.
     * @throws Exception 
     */
    public function parseClientId(string $clientType): string
    {
        $clientId = match ($clientType) {
            'web' => config('oauth.apple.service_id'),      // Service ID for web
            'ios' => config('oauth.apple.bundle_id'),       // Bundle ID for iOS
            'android' => config('oauth.apple.service_id'),  // Package Name for Android
            default => throw new \Exception('Unsupported device type'),
        };

        return $clientId;
    }

    /**
     * Generate a signed JWT.
     * @param string $clientId Should be the service ID in case of web apps, or the bundle ID in case of iOS. For Android the service ID is used aswell, as the sign in dialog happens in a webview.
     *
     * @return string
     */
    public function generateClientSecret(string $clientId): string
    {
        // Load configurations from the Laravel config file
        $teamId = config('oauth.apple.team_id'); // 10-character Team ID
        $keyId = config('oauth.apple.key_id'); // 10-character Key ID
        $privateKey = config('oauth.apple.private_key'); // Contents of the private key file
        
        // Define the expiration and issued-at times
        $now = new DateTimeImmutable('@' . time());
        $expiration = $now->modify('+1 months'); // Ensure it's within 6 months
        
        // Create the signer and key
        $signer = new Sha256();
        $key = InMemory::plainText($privateKey);


        $builder = new Builder(new JoseEncoder(), ChainedFormatter::default());
        
        // Build the token
        $token = $builder
            ->issuedBy($teamId) // iss claim
            ->issuedAt($now) // iat claim
            ->expiresAt($expiration) // exp claim
            ->permittedFor('https://appleid.apple.com') // aud claim
            ->relatedTo($clientId) // sub claim
            ->withHeader('alg', 'ES256') // Algorithm
            ->withHeader('kid', $keyId) // Key ID
            ->getToken($signer, $key); // Sign the token
        
        return $token->toString();
    }

    /**
     * Verify the authorization code with Apple.
     * @param string $deviceType 
     * @param string $authCode
     * @return array|false The response from Apple.
     * @throws Exception 
     * @throws InvalidKeyProvided 
     * @throws CannotEncodeContent 
     * @throws CannotSignPayload 
     * @throws ConversionFailed 
     */
    public function verifyAuthCode(string $deviceType, string $authCode): array|false
    {
        $clientId = $this->parseClientId($deviceType);
        $appleJwt = $this->generateClientSecret($clientId);
        $response = Http::asForm()->post('https://appleid.apple.com/auth/token', [
            'client_id' => $clientId,
            'client_secret' => $appleJwt,
            'code' => $authCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('oauth.apple.redirect_uri'),
        ]);
        $payload = $response->json();
        if (!isset($payload['id_token'])) {
            return false;
        }

        $parser = new Parser(new JoseEncoder());
        try {
            $token = $parser->parse($payload['id_token']);
            assert($token instanceof UnencryptedToken);
            $payload = $token->claims()->all();
        } catch (\Exception $e) {
            return false;
        }

        return $payload;
    }
}
