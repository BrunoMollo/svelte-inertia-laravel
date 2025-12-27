# import config.
# You can change the default config with `make env=".env" up`
env ?= .env
include $(env)
export $(shell sed 's/=.*//' $(env))

up:
	docker compose --env-file $(env) watch

down:
	docker compose down

bash:
	docker exec -ti laravel-dev bash

logs:
	docker exec -ti laravel-dev tail -f /app/storage/logs/laravel.log

