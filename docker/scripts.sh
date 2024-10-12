#!/bin/bash

CANTR_ROOT="$PWD/.."
LAMP_CONTAINER_ID="docker_lamp_1"

run_composer() {
	echo "Runing composer..."
	docker exec $LAMP_CONTAINER_ID bash -c 'composer selfupdate'
	docker exec $LAMP_CONTAINER_ID bash -c 'cd /app && composer install'
}

rebuild_templates() {
	echo "Rebuilding templates..."
	cp "$CANTR_ROOT/lib/templatemgr.php" "$CANTR_ROOT/www/"
	wget -qO- localhost:8083/templatemgr.php?rebuildall=1 &> /dev/null
}

rebuild_class_loader() {
	echo "Rebuilding class loader..."
	docker exec $LAMP_CONTAINER_ID bash -c 'cd /app/storage && php loader.php' > /dev/null
}

setup_db() {
	echo "Setting up the database..."
	docker exec $LAMP_CONTAINER_ID bash -c 'mysql -u root --password="" -e "SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'"'"'ONLY_FULL_GROUP_BY'"', ''"'));"'
	docker exec $LAMP_CONTAINER_ID bash -c 'mysql -u root --password="" -e "UPDATE mysql.user SET Host = '"'"'%'"'"' WHERE User = '"'"'root'"'"'; FLUSH PRIVILEGES;"'
	docker exec $LAMP_CONTAINER_ID bash -c 'cd /app/storage && php setup_db.php'
}

db_for_int_test() {
        echo "Recreating the database for integration tests"
        docker exec $LAMP_CONTAINER_ID bash -c 'cd /app/storage && php db_for_int_test.php' 2> /dev/null
}

run_tests() {
      docker exec $LAMP_CONTAINER_ID bash -c 'php /app/vendor/phpunit/phpunit/phpunit --bootstrap /app/tests/includes.php --configuration /app/phpunit.xml.dist'
}

run_int_tests() {
      docker exec  $LAMP_CONTAINER_ID bash -c 'php /app/vendor/phpunit/phpunit/phpunit --bootstrap /app/tests/includes.php --configuration /app/integration.phpunit.xml'
}

create_docker_config() {
	docker exec $LAMP_CONTAINER_ID bash -c 'cd /app/docker && php create_docker_config.php'
	if [ -f "../config/config.json.temp" ]; then
		cp -n ../config/config.json.temp ../config/config.json
		echo "Moved file config/config.json.temp to config/config.json"
		docker exec $LAMP_CONTAINER_ID bash -c 'rm /app/config/config.json.temp'
		echo "Removed file config/config.json.temp"
	fi
}

react_prod_build() {
  mkdir -p ../www/react
	docker run -v `pwd`/..:/app/ node:12.16.2-stretch bash -c "cd /app/cantr-frontend/ && yarn install && yarn cantr-build"
}

mysql() {
	docker exec -ti $LAMP_CONTAINER_ID bash -c 'mysql -u root --password="" cantr_test'
}

run_api_composer() {
	echo "Runing composer for the API..."
	docker exec $LAMP_CONTAINER_ID bash -c 'cd /app/www/api && composer install'
}

if [ "$1" == "composer" ]; then
	run_composer
elif [ "$1" == "templates" ]; then
	rebuild_templates
elif [ "$1" == "classes" ]; then
	rebuild_class_loader
elif [ "$1" == "db" ]; then
	setup_db
elif [ "$1" == "int_test_db" ]; then
        db_for_int_test
elif [ "$1" == "config" ]; then
    create_docker_config
elif [ "$1" == "test" ]; then
    run_tests
elif [ "$1" == "int_test" ]; then
    run_int_tests
elif [ "$1" == "mysql" ]; then
	mysql
elif [ "$1" == "react_prod_build" ]; then
	react_prod_build
elif [ "$1" == "" ]; then  # run everything you may need
	run_composer
	create_docker_config
	setup_db
	rebuild_templates
	rebuild_class_loader
	db_for_int_test
	react_prod_build
	run_api_composer
fi
