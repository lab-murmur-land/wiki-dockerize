up:
	docker-compose -f stack/docker-compose.yml up --wait
down:
	docker-compose -f stack/docker-compose.yml down
build:
	docker-compose -f stack/docker-compose.yml build --progress=plain
clean:
	docker-compose -f stack/docker-compose.yml down --volumes --remove-orphans
init: up
	docker-compose -f stack/docker-compose.yml exec mediawiki /tmp/init.bash
logs:
	docker-compose -f stack/docker-compose.yml logs -f
shell_wiki:
	docker-compose -f stack/docker-compose.yml exec -it mediawiki bash

restart: down up
reload: build up

reinstall: clean init
