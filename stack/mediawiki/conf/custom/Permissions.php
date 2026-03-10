<?php

$wgGroupPermissions['sysop']['edit-guides'] = true;
$wgGroupPermissions['editor']['edit-guides'] = true;
$wgGroupPermissions['bureaucrat']['edit-dev'] = true;
// Rehber ad alanını sadece 'editor' grubu düzenleyebilsin
$wgNamespaceProtection[NS_REHBER] = [ 'edit-guides' ];
$wgNamespaceProtection[NS_DEV] = [ 'edit-dev' ];
