include config.env
export

up:
	docker-compose -f stack/docker-compose.yml up --wait
down:
	docker-compose -f stack/docker-compose.yml down
build:
	docker-compose -f stack/docker-compose.yml build --progress=plain ${flags}
clean:
	docker-compose -f stack/docker-compose.yml down --volumes --remove-orphans

init: up
	docker-compose -f stack/docker-compose.yml exec mediawiki /tmp/init.bash

logs:
	docker-compose -f stack/docker-compose.yml logs -f
shell_wiki:
	docker-compose -f stack/docker-compose.yml exec -it mediawiki bash
wiki:
	docker-compose -f stack/docker-compose.yml exec mediawiki /tmp/install_deps.bash
restart: down up
reload: build up

reinstall: clean init

dev-init:
	mkdir -p /tmp/${COMPOSE_PROJECT_NAME}_vendor
__dbg:
	echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"
