#!/usr/bin/env bash
set -euo pipefail

cmd="${1:-help}"
shift || true

init_db(){
	php maintenance/run.php installPreConfigured
	php maintenance/run.php createAndPromote --sysop --bureaucrat "$1" "$2"
}

update_db(){
	php /var/www/html/maintenance/run.php update --quick
}

run_php(){
	php /var/www/html/maintenance/run.php "$@"
}

case "$cmd" in
  composer-update)
	COMPOSER_ALLOW_SUPERUSER=1 composer update --no-dev --no-security-blocking --no-interaction -o
    ;;
  composer-install)
    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-security-blocking --no-interaction -o
    cd /var/www/html/extensions/VisualEditor && git submodule update --init --recursive # why not needed in container?
	chmod a+x /var/www/html/extensions/SyntaxHighlight_GeSHi/pygments/pygmentize
	chmod +x /var/www/html/extensions/Scribunto/includes/Engines/LuaStandalone/binaries/lua5_1_5_linux_64_generic/lua
    ;;
	init-db)
		init_db "$@"
	;;
	update-db)
		update_db "$@"
	;;
	run-php)
		run_php "$@"
	;;
  help|*)
    echo "Usage: $0 {composer-install|init-db <AdminUsername> <AdminPassword>|update-db|run-php <MaintenanceScript> [args...]|help}"
    ;;
esac
