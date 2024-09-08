# gerror

A small service to collect and classify errors coming from GMod.

It requires a web stack to be accessible. Something like NGINX, PHP, MySQL, your own host and your own domain. The database is in the sqlbackup folder and the configuration in the config folder.

For now the API is only in use by gm_construct 13 beta Error library: https://github.com/Xalalau/GMod-Lua-Error-API

Server requirements: https://github.com/Xalalau/docker-stacks/tree/master/gmoderror

SQL structure: https://github.com/Xalalau/gerror/blob/main/sqlbackup/gerror.sql

My usage: https://gerror.xalalau.com/
