<?php

namespace MediaWiki\Extension\Auth42;

use MediaWiki\Extension\PluggableAuth\PluggableAuth;
use MediaWiki\Session\SessionManager;
use MediaWiki\User\UserIdentity;

class Auth42 extends PluggableAuth {

    private const AUTHORIZE_URL = 'https://api.intra.42.fr/oauth/authorize';
    private const TOKEN_URL     = 'https://api.intra.42.fr/oauth/token';
    private const USER_INFO_URL = 'https://api.intra.42.fr/v2/me';

    public function authenticate(
        ?int &$id,
        ?string &$username,
        ?string &$realname,
        ?string &$email,
        ?string &$errorMessage
    ): bool {
        $request  = \RequestContext::getMain()->getRequest();
        $response = $request->response();
        $code     = $request->getVal( 'code' );

        if ( $code !== null ) {
            return $this->handleCallback( $code, $id, $username, $realname, $email, $errorMessage );
        }

        $state   = bin2hex( random_bytes( 16 ) );
        $session = SessionManager::getGlobalSession();
        $session->set( 'auth42_state', $state );
        $session->save();

        $params = http_build_query( [
            'client_id'     => $this->getData()->get( 'ClientID' ),
            'redirect_uri'  => $this->getData()->get( 'RedirectURI' ),
            'response_type' => 'code',
            'scope'         => 'public',
            'state'         => $state,
        ] );

        $response->header( 'Location: ' . self::AUTHORIZE_URL . '?' . $params );
        exit;
    }

    private function handleCallback(
        string $code,
        ?int &$id,
        ?string &$username,
        ?string &$realname,
        ?string &$email,
        ?string &$errorMessage
    ): bool {
        $tokenResponse = $this->httpPost( self::TOKEN_URL, [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->getData()->get( 'ClientID' ),
            'client_secret' => $this->getData()->get( 'ClientSecret' ),
            'redirect_uri'  => $this->getData()->get( 'RedirectURI' ),
            'code'          => $code,
        ] );

        if ( !$tokenResponse || !isset( $tokenResponse['access_token'] ) ) {
            $errorMessage = 'Auth42: token alinamadi.';
            return false;
        }

        $userInfo = $this->httpGet( self::USER_INFO_URL, $tokenResponse['access_token'] );

        if ( !$userInfo || !isset( $userInfo['login'] ) ) {
            $errorMessage = 'Auth42: kullanici bilgisi alinamadi.';
            return false;
        }

        $id       = null;
        $username = $userInfo['login'];
        $realname = $userInfo['displayname'] ?? $userInfo['login'];
        $email    = $userInfo['email'] ?? '';

        return true;
    }

    public function saveExtraAttributes( int $id ): void {
    }

    public function deauthenticate( UserIdentity &$user ): void {
    }

    private function httpPost( string $url, array $params ): ?array {
        $ch = curl_init( $url );
        curl_setopt_array( $ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query( $params ),
            CURLOPT_HTTPHEADER     => [ 'Content-Type: application/x-www-form-urlencoded' ],
            CURLOPT_TIMEOUT        => 10,
        ] );
        $body = curl_exec( $ch );
        curl_close( $ch );
        return $body ? json_decode( $body, true ) : null;
    }

    private function httpGet( string $url, string $token ): ?array {
        $ch = curl_init( $url );
        curl_setopt_array( $ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [ 'Authorization: Bearer ' . $token ],
            CURLOPT_TIMEOUT        => 10,
        ] );
        $body = curl_exec( $ch );
        curl_close( $ch );
        return $body ? json_decode( $body, true ) : null;
    }
}
