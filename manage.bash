#!/usr/bin/env bash
set -euo pipefail

# Load config
set -a
source config.env
set +a

COMPOSE="docker-compose -f stack/docker-compose.yml"

maintaince-init-db(){
	$COMPOSE exec mediawiki /tmp/maintaince.bash init-db AdminKullaniciAdin AdminSifren123
}
maintaince-composer-install(){
	$COMPOSE exec mediawiki /tmp/maintaince.bash composer-install

}
down-volumes(){
	$COMPOSE down --volumes --remove-orphans
}

up(){
	$COMPOSE up --wait
}

cmd="${1:-help}"
shift || true
case "$cmd" in
  up)
    up
    ;;
  down)
    $COMPOSE down
    ;;
  build)
    $COMPOSE build --progress=plain "$@"
    ;;
  clean)
    down-volumes
    ;;
  init)
	maintaince-composer-install; maintaince-init-db;
    ;;
  logs)
    $COMPOSE logs -f
    ;;
  wiki_sh)
    $COMPOSE exec -it mediawiki bash
    ;;
  wiki_cli)
    $COMPOSE exec mediawiki /tmp/maintaince.bash "$@"
    ;;
  restart)
    $COMPOSE down
    up
    ;;
  reload)
    $COMPOSE build --progress=plain "$@"
    up
    ;;
  reinstall)
    down-volumes
    up
    $COMPOSE exec mediawiki /tmp/init.bash
    ;;
	compose)
	$COMPOSE "$@"
	;;
  dev-init)
    mkdir -p "/tmp/${COMPOSE_PROJECT_NAME}_vendor"
	mkdir -p "/tmp/${COMPOSE_PROJECT_NAME}_extensions"
	mkdir -p "/tmp/${COMPOSE_PROJECT_NAME}_images"
	cp -r ./stack/images/* "/tmp/${COMPOSE_PROJECT_NAME}_images/"
	;;
	fix-images-permissions)
		$COMPOSE exec mediawiki chown -R www-data:www-data /var/www/html/images
    ;;
  __dbg)
    echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"
    ;;
  help|*)
    echo "Usage: $0 {up|down|build|clean|init|logs|wiki_sh|wiki_cli|restart|reload|reinstall|compose|dev-init|fix-images-permissions|__dbg}"
    ;;
esac
