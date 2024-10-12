Please copy `config.json.default` to `config.json` in the same directory.

It's necessary to set up database credentials to make the Cantr server work.
`error_log` file should be either absolute or relative to Cantr's `X.cantr.net` directory.
`database.errorLogFile` is used as by PHP and it must be either absolute or relative to DOCUMENT_ROOT (`X.cantr.net/www`).

If you want to quickly run development environment using Docker, please read `README.md` in the project's root. 
