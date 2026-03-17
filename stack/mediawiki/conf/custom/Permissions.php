<?php

// Rehber ad alanını sadece 'editor' grubu düzenleyebilsin
$wgNamespaceProtection[NS_REHBER] = [ 'edit-guides' ];
$wgNamespaceProtection[NS_DEV] = [ 'edit-dev' ];

$wgGroupPermissions["*"]["edit"] = false;
# Normal kayıt kapalı
$wgGroupPermissions['*']['createaccount'] = false;
# PluggableAuth (42 OAuth) ile otomatik hesap oluşturmaya izin ver
$wgGroupPermissions['*']['autocreateaccount'] = true;
# Sadece adminler onaylayabilir
// $wgGroupPermissions['sysop']['createaccount'] = true;

$wgGroupPermissions['sysop']['editinterface'] = true;
$wgGroupPermissions['sysop']['editsitecss'] = true;
$wgGroupPermissions['sysop']['editsitejs'] = true;

$wgGroupPermissions['sysop']['edit-guides'] = true;
$wgGroupPermissions['editor']['edit-guides'] = true;
$wgGroupPermissions['bureaucrat']['edit-dev'] = true;

# Approved Revs
$egApprovedRevsBlankIfUnapproved = true;        # Onaylanmamış sayfalar boş görünsün
$egApprovedRevsAutomaticApprovals = true;        # approverevisions yetkisi olan (sysop) editleri otomatik onaylanır
$egApprovedRevsShowNotApprovedMessage = true;    # Onaysız sayfalarda mesaj göster

# Sadece sysop onaylayabilsin
$wgGroupPermissions['sysop']['deleterevision'] = true;
$wgGroupPermissions['sysop']['approverevisions'] = true;

# Sadece sysop "en son sürümü gör" linkini görsün
$wgGroupPermissions['*']['viewlinktolatest'] = false;
$wgGroupPermissions['sysop']['viewlinktolatest'] = true;

# Moderation
$wgGroupPermissions['sysop']['moderation'] = true;
$wgGroupPermissions['sysop']['moderation-checkuser'] = true;
$wgGroupPermissions['sysop']['skip-moderation'] = true;      # Sysop editleri kuyruğa girmez
$wgGroupPermissions['sysop']['skip-move-moderation'] = true; # Sysop taşımaları kuyruğa girmez
// MediaWiki_default page autoapproved
$wgGroupPermissions['sysop']['autoapprove'] = true; // ?


# Moderation onayı → ApprovedRevs otomatik onay
# Moderation tüm normal kullanıcı editlerini denetlediği için,
# PageSaveComplete'e ulaşan bir edit ya sysop tarafından yapılmıştır
# ya da Moderation'dan onaylanmıştır. Her iki durumda da ApprovedRevs'te onayla.
// $wgHooks['PageSaveComplete'][] = static function (
// 	$wikiPage, $user, $summary, $flags, $revisionRecord, $editResult
// ) {
// 	$title = $wikiPage->getTitle();
// 	if ( !ApprovedRevs::pageIsApprovable( $title ) ) {
// 		return;
// 	}
// 	ApprovedRevs::saveApprovedRevIDInDB(
// 		$title, $revisionRecord->getID(), $user, true
// 	);
// };

# Güvenlik: Bir revizyon silindiğinde/gizlendiğinde, eğer o revizyon
# ApprovedRevs'te "onaylı" olarak kayıtlıysa, onayı otomatik kaldır.
# Bu, silinmiş bir revizyonun admin sayfasını patlatmasını önler.
$wgHooks['ArticleRevisionVisibilitySet'][] = static function (
	$title, $ids, $visibilityChangeMap
) {
	$dbw = \MediaWiki\MediaWikiServices::getInstance()
		->getDBLoadBalancerFactory()
		->getPrimaryDatabase();
	foreach ( $ids as $revId ) {
		$revDeleted = $dbw->newSelectQueryBuilder()
			->from( 'revision' )
			->select( 'rev_deleted' )
			->where( [ 'rev_id' => $revId ] )
			->caller( __METHOD__ )
			->fetchField();
		if ( $revDeleted && (int)$revDeleted > 0 ) {
			$dbw->newDeleteQueryBuilder()
				->deleteFrom( 'approved_revs' )
				->where( [ 'rev_id' => $revId ] )
				->caller( __METHOD__ )
				->execute();
		}
	}
};
# Güvenlik: rev_deleted > 0 olan revizyonların onaylanmasını engelle.
# ApprovedRevs bir revizyonu onayladığında, revizyon gizliyse onayı anında geri al.
$wgHooks['ApprovedRevsRevisionApproved'][] = static function (
	$output, $title, $rev_id, $content
) {
	$dbw = \MediaWiki\MediaWikiServices::getInstance()
		->getDBLoadBalancerFactory()
		->getPrimaryDatabase();
	$revDeleted = $dbw->newSelectQueryBuilder()
		->from( 'revision' )
		->select( 'rev_deleted' )
		->where( [ 'rev_id' => $rev_id ] )
		->caller( __METHOD__ )
		->fetchField();
	if ( $revDeleted && (int)$revDeleted > 0 ) {
		// Silinmiş/gizlenmiş revizyon onaylı olamaz, onayı kaldır
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'approved_revs' )
			->where( [ 'rev_id' => $rev_id ] )
			->caller( __METHOD__ )
			->execute();
	}
};


