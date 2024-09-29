# gerror

A small service to collect and classify errors coming from GMod.

It requires a web stack to be accessible. Something like NGINX, PHP, MySQL, your own host and your own domain. The database is in the sqlbackup folder and the configuration in the config folder.

For now the API is only in use by gm_construct 13 beta Error library: https://github.com/Xalalau/GMod-Lua-Error-API

Server requirements (read this docker compose file): [gmoderror.zip](https://github.com/user-attachments/files/17177742/gmoderror.zip)

My usage: https://gerror.xalalau.com/
