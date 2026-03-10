#!/usr/bin/env bash
set -euo pipefail

cmd="${1:-help}"
shift || true

init_db(){
	php maintenance/run.php installPreConfigured
	php maintenance/run.php createAndPromote --sysop --bureaucrat "$1" "$2"
}

case "$cmd" in
  composer-install)
    COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-security-blocking --no-interaction -o
    cd /var/www/html/extensions/VisualEditor && git submodule update --init --recursive
    ;;
	init-db)
		init_db "$@"
	;;
  help|*)
    echo "Usage: $0 {composer-install|init-db <AdminUsername> <AdminPassword>}"
    ;;
esac
