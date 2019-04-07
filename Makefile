docker/up:
	docker-compose up -d --build
	sleep 5 # mysqlのupを雑に待つ
	docker-compose exec dev-db /workspace/init.sh

docker/down:
	docker-compose down
