<?php

// namespace MediaWiki\Extension\Auth42;

// use MediaWiki\Extension\PluggableAuth\PluggableAuth;
// use MediaWiki\Session\SessionManager;
// use MediaWiki\User\UserIdentity;
// use MediaWiki\MediaWikiServices;
// use MediaWiki\Revision\SlotRecord;
// use MediaWiki\CommentStore\CommentStoreComment;
// use WikitextContent;
// use Title;

// class Auth42 extends PluggableAuth {

// 	private const AUTHORIZE_URL = 'https://api.intra.42.fr/oauth/authorize';
// 	private const TOKEN_URL     = 'https://api.intra.42.fr/oauth/token';
// 	private const USER_INFO_URL = 'https://api.intra.42.fr/v2/me';

// 	public function authenticate(
// 		?int &$id,
// 		?string &$username,
// 		?string &$realname,
// 		?string &$email,
// 		?string &$errorMessage
// 	): bool {
// 		$request  = \RequestContext::getMain()->getRequest();
// 		$response = $request->response();
// 		$code     = $request->getVal( 'code' );

// 		if ( $code !== null ) {
// 			return $this->handleCallback( $code, $id, $username, $realname, $email, $errorMessage );
// 		}

// 		$state   = bin2hex( random_bytes( 16 ) );
// 		$session = SessionManager::getGlobalSession();
// 		$session->set( 'auth42_state', $state );
// 		$session->save();

// 		$params = http_build_query( [
// 			'client_id'     => $this->getData()->get( 'ClientID' ),
// 			'redirect_uri'  => $this->getData()->get( 'RedirectURI' ),
// 			'response_type' => 'code',
// 			'scope'         => 'public',
// 			'state'         => $state,
// 		] );

// 		$response->header( 'Location: ' . self::AUTHORIZE_URL . '?' . $params );
// 		exit;
// 	}

// 	private function handleCallback(
// 		string $code,
// 		?int &$id,
// 		?string &$username,
// 		?string &$realname,
// 		?string &$email,
// 		?string &$errorMessage
// 	): bool {
// 		$tokenResponse = $this->httpPost( self::TOKEN_URL, [
// 			'grant_type'    => 'authorization_code',
// 			'client_id'     => $this->getData()->get( 'ClientID' ),
// 			'client_secret' => $this->getData()->get( 'ClientSecret' ),
// 			'redirect_uri'  => $this->getData()->get( 'RedirectURI' ),
// 			'code'          => $code,
// 		] );

// 		if ( !$tokenResponse || !isset( $tokenResponse['access_token'] ) ) {
// 			$errorMessage = 'Auth42: token alınamadı: ' . json_encode( $tokenResponse );
// 			return false;
// 		}

// 		$userInfo = $this->httpGet( self::USER_INFO_URL, $tokenResponse['access_token'] );

// 		if ( !$userInfo || !isset( $userInfo['login'] ) ) {
// 			$errorMessage = 'Auth42: kullanıcı bilgisi alınamadı.';
// 			return false;
// 		}

// 		$id       = null;
// 		$username = $userInfo['login'];
// 		$realname = $userInfo['displayname'] ?? $userInfo['login'];
// 		$email    = $userInfo['email'] ?? '';

// 		// Avatar URL'ini global cache'e yaz
// 		$cache = MediaWikiServices::getInstance()->getMainObjectStash();
// 		$key   = $cache->makeKey( 'auth42-avatar', $userInfo['login'] );
// 		$cache->set( $key, $userInfo['image']['link'] ?? '', 300 );

// 		return true;
// 	}

// 	public function deauthenticate( UserIdentity &$user ): void {}

// 	public function saveExtraAttributes( int $id ): void {
// 		error_log( "Auth42 saveExtraAttributes called for id=" . $id );
// 		$services  = MediaWikiServices::getInstance();
// 		$user      = $services->getUserFactory()->newFromId( $id );
// 		$cache     = $services->getMainObjectStash();
// 		$key       = $cache->makeKey( 'auth42-avatar', $user->getName() );
// 		$avatarUrl = $cache->get( $key );
// 		error_log( "Auth42 avatarUrl=" . var_export($avatarUrl, true) );
		
// 		// if ( !$avatarUrl ) {
// 		// 	return;
// 		// }

// 		$title = Title::makeTitle( NS_USER, $user->getName() );
// 		$page  = $services->getWikiPageFactory()->newFromTitle( $title );

// 		// if ( $page->exists() ) {
// 		// 	return;
// 		// }

// 		$wikitext = "== 42 Profil ==\n" .
// 			"<div style=\"float:right; margin:10px;\">" .
// 			"<img src=\"" . htmlspecialchars( $avatarUrl ) . "\" width=\"150\" style=\"border-radius:50%\" /></div>\n";

// 		$content = new WikitextContent( $wikitext );
// 		$updater = $page->newPageUpdater(
// 			$services->getUserFactory()->newSystemUser( 'Auth42' )
// 		);
// 		$updater->setContent( SlotRecord::MAIN, $content );
// 		$updater->saveRevision(
// 			CommentStoreComment::newUnsavedComment( '42 profil sayfası oluşturuldu' )
// 		);

// 		$cache->delete( $key );
// 	}
	
// 	public function onUserLoggedIn( User $user ): void {
// 		$services  = MediaWikiServices::getInstance();
// 		$cache     = $services->getMainObjectStash();
// 		$key       = $cache->makeKey( 'auth42-avatar', $user->getName() );
// 		$avatarUrl = $cache->get( $key );

// 		if ( !$avatarUrl ) {
// 			return;
// 		}

// 		$title = Title::makeTitle( NS_USER, $user->getName() );
// 		$page  = $services->getWikiPageFactory()->newFromTitle( $title );

// 		$wikitext = "== 42 Profil ==\n" .
// 			"<div style=\"float:right; margin:10px;\">" .
// 			"<img src=\"" . htmlspecialchars( $avatarUrl ) . "\" width=\"150\" style=\"border-radius:50%\" /></div>\n";

// 		$content = new WikitextContent( $wikitext );
// 		$updater = $page->newPageUpdater(
// 			$services->getUserFactory()->newSystemUser( 'Auth42' )
// 		);
// 		$updater->setContent( SlotRecord::MAIN, $content );
// 		$updater->saveRevision(
// 			CommentStoreComment::newUnsavedComment( '42 profil sayfası güncellendi' )
// 		);

// 		$cache->delete( $key );
// 	}
// 	private function httpPost( string $url, array $params ): ?array {
// 		$ch = curl_init( $url );
// 		curl_setopt_array( $ch, [
// 			CURLOPT_RETURNTRANSFER => true,
// 			CURLOPT_POST           => true,
// 			CURLOPT_POSTFIELDS     => http_build_query( $params ),
// 			CURLOPT_HTTPHEADER     => [ 'Content-Type: application/x-www-form-urlencoded' ],
// 			CURLOPT_TIMEOUT        => 10,
// 		] );
// 		$body = curl_exec( $ch );
// 		curl_close( $ch );
// 		return $body ? json_decode( $body, true ) : null;
// 	}

// 	private function httpGet( string $url, string $token ): ?array {
// 		$ch = curl_init( $url );
// 		curl_setopt_array( $ch, [
// 			CURLOPT_RETURNTRANSFER => true,
// 			CURLOPT_HTTPHEADER     => [ 'Authorization: Bearer ' . $token ],
// 			CURLOPT_TIMEOUT        => 10,
// 		] );
// 		$body = curl_exec( $ch );
// 		curl_close( $ch );
// 		return $body ? json_decode( $body, true ) : null;
// 	}
// }


namespace MediaWiki\Extension\Auth42;

use MediaWiki\Extension\PluggableAuth\PluggableAuth;
use MediaWiki\Session\SessionManager;
use MediaWiki\User\UserIdentity;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\CommentStore\CommentStoreComment;
use WikitextContent;
use Title;

class Auth42 extends PluggableAuth {

    private const AUTHORIZE_URL = 'https://api.intra.42.fr/oauth/authorize';
    private const TOKEN_URL     = 'https://api.intra.42.fr/oauth/token';
    private const USER_INFO_URL = 'https://api.intra.42.fr/v2/me';

    /** Avatar URL'ini aynı request içinde taşır (yeni hesaplar için). */
    private static ?string $pendingAvatarUrl = null;

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
            $errorMessage = 'Auth42: token alınamadı.';
            return false;
        }

        $userInfo = $this->httpGet( self::USER_INFO_URL, $tokenResponse['access_token'] );

        if ( !$userInfo || !isset( $userInfo['login'] ) ) {
            $errorMessage = 'Auth42: kullanıcı bilgisi alınamadı.';
            return false;
        }

        $id       = null;
        $username = $userInfo['login'];
        $realname = $userInfo['displayname'] ?? $userInfo['login'];
        $email    = $userInfo['email'] ?? '';

        // Avatar URL'ini hem static var hem session'a yaz
        $avatarUrl = $userInfo['image']['link'] ?? '';
        self::$pendingAvatarUrl = $avatarUrl;

        // Session'a da kaydet — UserLoginComplete hook'u için (mevcut kullanıcılar)
        $session = SessionManager::getGlobalSession();
        $session->set( 'auth42_avatar_url', $avatarUrl );
        $session->save();

        return true;
    }

    /**
     * Kullanıcı sayfasına Auth42 avatar bloğunu ekler veya günceller.
     * Giriş yapan kullanıcıyı yazar (actor) olarak kullanır.
     */
    private static function upsertAvatarOnUserPage( \User $user, string $avatarUrl ): bool {
        $services = MediaWikiServices::getInstance();
        $username = $user->getName();

        error_log( "Auth42 upsert: $username icin avatar yaziliyor..." );

        $title = $services->getTitleFactory()->makeTitle( NS_USER, $username );
        $wikiPage = $services->getWikiPageFactory()->newFromTitle( $title );

        $markerStart = '<!-- Auth42Avatar:start -->';
        $markerEnd   = '<!-- Auth42Avatar:end -->';
        $avatarBlock = $markerStart . "\n" .
            '<div style="float:right; margin:10px;">' .
            '<img src="' . htmlspecialchars( $avatarUrl ) . '" width="150" style="border-radius:50%" />' .
            "</div>\n" . $markerEnd;

        // Mevcut sayfa içeriğini oku (parametresiz — audience varsayılan FOR_PUBLIC)
        $existingText = '';
        if ( $wikiPage->exists() ) {
            $rev = $wikiPage->getRevisionRecord();
            if ( $rev ) {
                $slot = $rev->getContent( SlotRecord::MAIN );
                if ( $slot ) {
                    $existingText = $slot->serialize();
                }
            }
        }

        // Eski avatar bloğu varsa güncelle, yoksa başa ekle
        if ( strpos( $existingText, $markerStart ) !== false
             && strpos( $existingText, $markerEnd ) !== false ) {
            $newText = preg_replace(
                '/' . preg_quote( $markerStart, '/' ) . '.*?' . preg_quote( $markerEnd, '/' ) . '/s',
                $avatarBlock,
                $existingText,
                1
            );
        } else {
            $newText = $avatarBlock . "\n\n" . ltrim( $existingText );
        }

        $content = new WikitextContent( trim( $newText ) );
        $updater = $wikiPage->newPageUpdater( $user );
        $updater->setContent( SlotRecord::MAIN, $content );
        $comment = CommentStoreComment::newUnsavedComment( 'Auth42: avatar güncellendi' );
        $updater->saveRevision( $comment );

        $status = $updater->getStatus();
        if ( !$status->isOK() ) {
            error_log( "Auth42 Hata: Sayfa kaydedilemedi — " . $status->getMessage()->text() );
            return false;
        }

        error_log( "Auth42 OK: $username profil sayfasi guncellendi." );
        return true;
    }

    /**
     * PluggableAuth tarafından authenticate() başarılı olduktan sonra çağrılır.
     * Aynı request içinde çalıştığı için static $pendingAvatarUrl güvenilirdir.
     */
    public function saveExtraAttributes( int $id ): void {
        error_log( "Auth42 saveExtraAttributes: id=$id, pendingAvatar=" . ( self::$pendingAvatarUrl ?: 'YOK' ) );

        $avatarUrl = self::$pendingAvatarUrl;
        self::$pendingAvatarUrl = null;

        if ( !$avatarUrl ) {
            return;
        }

        // Kullanıcıyı DB'den yükle
        $user = \User::newFromId( $id );
        $user->load();

        if ( !$user || $user->isAnon() ) {
            error_log( "Auth42: id=$id icin kullanici yuklenemedi." );
            return;
        }

        error_log( "Auth42: Kullanici=" . $user->getName() . ", avatar yazilacak." );

        if ( self::upsertAvatarOnUserPage( $user, $avatarUrl ) ) {
            error_log( "Auth42: Sayfa basariyla olusturuldu/guncellendi." );
        }
    }

    public function deauthenticate( UserIdentity &$user ): void {
        // Gerekli değilse boş bırakılabilir
    }

    /**
     * UserLoginComplete hook — her giriş sonrası avatar'ı User sayfasına yazar.
     * extension.json'da kayıtlı olmalıdır.
     */
    public static function onUserLoginComplete( \User $user, &$inject_html, $direct ) {
        $session = SessionManager::getGlobalSession();
        $avatarUrl = $session->get( 'auth42_avatar_url' );

        wfDebugLog( 'Auth42', "onUserLoginComplete: user=" . $user->getName() . ", avatar=" . ( $avatarUrl ?: 'YOK' ) );

        if ( !$avatarUrl ) {
            return;
        }

        // Session'dan sil — tek seferlik kullanım
        $session->remove( 'auth42_avatar_url' );
        $session->save();

        self::upsertAvatarOnUserPage( $user, $avatarUrl );
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
