#!/usr/bin/env bash
set -euo pipefail

# Load config
set -a
source config.env
source .env
set +a

if [ $DEV -eq 0 ]; then
	DATA_DIR="/var/data/"
else
	DATA_DIR="/tmp/"
fi

export DATA_DIR

COMPOSE="docker compose -f stack/docker-compose.yml"

maintaince-init-db(){
	$COMPOSE exec mediawiki /tmp/maintaince.bash init-db $WIKI_ADMIN_USER $WIKI_ADMIN_PASSWORD
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

up-wiki (){
	$COMPOSE up --wait mediawiki
}

dev_init(){
	sudo mkdir -p "$DATA_DIR"
	sudo mkdir -p "$DATA_DIR/${COMPOSE_PROJECT_NAME}_vendor"
	sudo mkdir -p "$DATA_DIR/${COMPOSE_PROJECT_NAME}_extensions"
	sudo mkdir -p "$DATA_DIR/${COMPOSE_PROJECT_NAME}_images"
	sudo mkdir -p "$DATA_DIR/${COMPOSE_PROJECT_NAME}_composer_cache"
	sudo mkdir -p "$DATA_DIR/${COMPOSE_PROJECT_NAME}_db"
	sudo cp -r ./stack/images/* "$DATA_DIR/${COMPOSE_PROJECT_NAME}_images/"
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
    $COMPOSE build --progress=plain mediawiki "$@"
    up-wiki
    ;;
  reinstall)
	dev_init
    down-volumes
    $COMPOSE build --progress=plain "$@"
    up
    maintaince-composer-install; maintaince-init-db;
    ;;
	dump-db)
		TIMESTAMP=$(date +%Y%m%d_%H%M%S)
		FILE_NAME="backup_${TIMESTAMP}.sql.gz"
		echo "Veritabanı yedeği alınıyor... ($FILE_NAME)"
		$COMPOSE exec $MYSQL_SERVER sh -c "export MYSQL_PWD='$MYSQL_PASSWORD'; mariadb-dump -u$MYSQL_USER --single-transaction --quick --routines --triggers $MYSQL_DATABASE" | gzip > "$FILE_NAME"
		if [ -s "$FILE_NAME" ]; then
			echo "Başarılı! Yedek dosyası oluşturuldu: $FILE_NAME"
			echo "Dosya Boyutu: $(du -sh "$FILE_NAME" | cut -f1)"
		else
			echo "Hata: Yedekleme başarısız oldu veya dosya boş."
			rm "$FILE_NAME"
		fi
	;;
	import-db)
		# Kullanım: ./script.sh import-db backup_dosyasi.sql.gz
		FILE_PATH="${1:-}"

		if [ -z "$FILE_PATH" ]; then
			echo "Hata: Lütfen bir yedek dosyası belirtin. Örn: ./script.sh import-db backup_2024.sql.gz"
			exit 1
		fi

		if [ ! -f "$FILE_PATH" ]; then
			echo "Hata: '$FILE_PATH' dosyası bulunamadı!"
			exit 1
		fi

		echo "$FILE_PATH veritabanına aktarılıyor... Bu işlem mevcut verileri silebilir!"
		read -p "Emin misiniz? (y/n): " confirm
		if [[ $confirm != [yY] ]]; then echo "İşlem iptal edildi."; exit 1; fi

		# .gz olup olmadığını kontrol et ve içeri aktar
		if [[ "$FILE_PATH" == *.gz ]]; then
			gunzip -c "$FILE_PATH" | $COMPOSE exec -T $MYSQL_SERVER sh -c "export MYSQL_PWD='$MYSQL_PASSWORD'; mariadb -u$MYSQL_USER --abort-source-on-error $MYSQL_DATABASE"
		else
			cat "$FILE_PATH" | $COMPOSE exec -T $MYSQL_SERVER sh -c "export MYSQL_PWD='$MYSQL_PASSWORD'; mariadb -u$MYSQL_USER --abort-source-on-error $MYSQL_DATABASE"
		fi

		echo "İşlem tamamlandı."
    ;;
	list-backups)
        echo "Mevcut yedekler:"
        ls -lh backup_*.gz 2>/dev/null || echo "Yedek bulunamadı."
    ;;
	compose)
	$COMPOSE "$@"
	;;
  	dev-init)
		dev_init
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
