# import config.
# You can change the default config with `make env=".env" up`
env ?= .env
include $(env)
export $(shell sed 's/=.*//' $(env))

up:
	docker compose  -f ./docker/docker-compose-dev.yml --env-file $(env) watch

down:
	docker compose -f ./docker/docker-compose-dev.yml down

bash:
	docker exec -ti laravel-dev bash

