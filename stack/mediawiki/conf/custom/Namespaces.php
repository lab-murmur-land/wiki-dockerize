<?php

define( "NS_REHBER", 3000 );
define( "NS_REHBER_TALK", 3001 );
define( "NS_ARSIV", 3002 );
define( "NS_DEV", 3003 );

$wgExtraNamespaces[NS_REHBER] = "Rehber";
$wgExtraNamespaces[NS_REHBER_TALK] = "Rehber_tartışma";
$wgExtraNamespaces[NS_ARSIV] = "Arşiv";
$wgExtraNamespaces[NS_DEV] = "Geliştirme";

// Alt sayfa destekleri
$wgNamespacesWithSubpages[NS_REHBER] = true;
$wgNamespacesWithSubpages[NS_DEV] = true;


$wgNamespacesToBeSearchedDefault = [
    NS_MAIN => true,
    NS_DEV  => true,
    NS_PROJECT => true
];
