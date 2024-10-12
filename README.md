# Cantr

This is main repository for Cantr game. Cantr works on (old) PHP 5 with apache and MySQL.

# Quickstart

It's possible to quickly start developing Cantr if you have docker compose.
This tutorial assumes you use Linux/Mac, for Windows it's necessary to use corresponding commands.

Start in the main directory of the repository and do the following:

```bash
cd docker
docker-compose up -d
./scripts.sh

```

Enter <http://localhost:8083> and you should see your own instance of Cantr.

You can now login using username: `cantr_test` and password: `test`.

### Database

If you'd like to connect to the database from outside of docker run `./scripts.sh mysql`

If you want to use your local mysql client, then it can be done with the following parameters:

```
mysql -u root -P 6666 --password="" -h 127.0.0.1
```

For more convenient access you can also use PhpMyAdmin, which is available on <http://localhost:8083/phpmyadmin>.
Use user `root` and empty password.

# Tests

Cantr has some tests, they are categorized into two groups:

 - unit tests, they test classes in isolation, run with `./scripts.sh test`
 - integration (database) tests, they test multiple classes and their communication with the real database, run with `./scripts.sh int_test`
 
It's not alawys possible to write tests, but if you can, please do it! 

Integration tests should use only the new (PDO) database API.
While it's acceptable to depend on classes with old mysql_* API for reading, 
if you need to set up the data or assert the db state using the connection using old API, 
then it's time to step back and rewrite these classes to PDO. 

# scripts.sh

`./scripts.sh` used without parameters runs all commands that are needed to set up Cantr environment.
Sometimes you may want to run only specific tasks, it's possible by passing a specific argument e.g. `./scripts.sh classes`.

The list of available commands:

 - `composer` download library dependencies used by Cantr
 
 - `templates` rebuild all Smarty templates
 
 - `classes` regenerate list of the classes in `AutoLoader.php`,
    must be run when class is added or renamed
 
 - `db` setup example database from the dump (if db did not exist)
    and perform db migrations (see `MigrationManager.php`) that were not run yet.
 
 - `int_test_db` creates a bare minimum database without any data for integration tests.
    It's based on `cantr_test` from `scripts.sh db`, so should be run always after that.  
 
 - `config` creates `config/config.json` which copies `config/config.json.default`
    and updates the values (like db connection parameters) to make environment run.
 
 - `test` run unit tests
 
 - `int_test` run integration tests. `cantr_int_test` database must exist and have the up-to-date structure
    (which is done by `./scripts.sh int_test_db`)
 
 - `mysql` run interactive terminal with mysql open. You can run queries to the `cantr_test` database.


