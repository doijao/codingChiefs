# Slim Framework 4 Skeleton Application


## Install the Application

Run this command from the directory in which you want to install your new Slim Framework application.

```bash
composer update
```

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writable.

Run docker
```bash
cd adorjao
docker-compose up -d
```

After that, open `http://localhost:9191/` in your browser.

Login to phpmyadmin,
``` username: user password: secret```

Import users.sql and products.sql

POST `http://localhost:8080/api/auth` 
`email: doi.jao@gmail.com`
`password: Password@123`

GET `http://localhost:8080/api/list/all` 


Codes are in 
```
index.php
app/routes.php
app/settings.php
app/dependencies.php
src/application/Helper/treeHelper
```