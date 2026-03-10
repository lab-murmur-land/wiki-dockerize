<?php

$wgGroupPermissions["*"]["edit"] = false;

$wgGroupPermissions['sysop']['edit-guides'] = true;
$wgGroupPermissions['editor']['edit-guides'] = true;
$wgGroupPermissions['bureaucrat']['edit-dev'] = true;
// Rehber ad alanını sadece 'editor' grubu düzenleyebilsin
$wgNamespaceProtection[NS_REHBER] = [ 'edit-guides' ];
$wgNamespaceProtection[NS_DEV] = [ 'edit-dev' ];




# Approved Revs
$egApprovedRevsBlankIfUnapproved = true;        # Onaylanmamış sayfalar boş görünsün
$egApprovedRevsAutomaticApprovals = false;       # Her edit manuel onay gerektirsin
$egApprovedRevsShowNotApprovedMessage = true;    # Onaysız sayfalarda mesaj göster

# Sadece sysop onaylayabilsin
$wgGroupPermissions['sysop']['approverevisions'] = true;
$wgGroupPermissions['user']['approverevisions'] = false;

# Sadece sysop "en son sürümü gör" linkini görsün
$wgGroupPermissions['*']['viewlinktolatest'] = false;
$wgGroupPermissions['sysop']['viewlinktolatest'] = true;





// $wgGroupPermissions['sysop']['review'] = true;
// $wgGroupPermissions['sysop']['autoreview'] = true;
// $wgGroupPermissions['user']['review'] = false;
// $wgGroupPermissions['user']['autoreview'] = false;

// # Onaylanmamış sayfaları sadece yetkili kullanıcılar görebilsin
// $wgGroupPermissions['*']['unreviewedpages'] = false;
// $wgGroupPermissions['user']['unreviewedpages'] = false;
// $wgGroupPermissions['sysop']['unreviewedpages'] = true;

// # Taslakları (draft) sadece sysop görsün
// $wgGroupPermissions['*']['viewdraft'] = false;
// $wgGroupPermissions['user']['viewdraft'] = false;
// $wgGroupPermissions['sysop']['viewdraft'] = true;

$wgUseRCPatrol = true;
$wgUseNPPatrol = true;   # Yeni sayfa patrol
$wgUseFilePatrol = true; # Dosya yükleme patrol


