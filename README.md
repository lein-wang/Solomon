# Solomon
基于Slim的轻量框架

支持接口+后台

支持mysql读写分离

支持单表一级缓存

使用Smarty模板

## nginx配置
```Bash
server {

    listen          8081;   
    server_name  192.168.115.197:8081;
    root   "xxx/public";
    index           index.php index.html index.htm;
    
    if ($time_iso8601 ~ '(\d{4}-\d{2}-\d{2})') {
        set $tttt $1;
    }
    access_log  logs/access-$tttt.log  main;
    autoindex off;
    
    location / {
      index  index.html index.htm index.php;
      #try_files $uri $uri/ /server.php?/$uri;
      try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php(.*)$ {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_split_path_info  ^((?U).+\.php)(/?.+)$;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        fastcgi_param  PATH_INFO  $fastcgi_path_info;
        fastcgi_param  PATH_TRANSLATED  $document_root$fastcgi_path_info;
        fastcgi_param  SERVER_NAME $host;
        include        fastcgi_params;
    }

    location ~ ^(\/static|\/assets) {
    	root xxx/resources;
    }
}
```
## 目录结构

public : 根目录

app ：接口和后台的controller + model

resources：静态文件+上传文件+smarty模板

logs：日志

plugin+util：常用的三方函数

## 配置文件

.env.example 改为 .env

数据库 / REDIS / SESSION 等

## 路由

public/route.php

例如：
```php
$app->post('/api/v1/{action}', \App\Controllers\Api\V1::class);
```
## 加view模板

resources/views/templates

## 加controller，model

app/controllers/Admin: 后台

app/controllers/Api: 接口

## 数据库交互

```php
$ur = (new User($this->container))->getUserRoles($user['id']);
```

或者直接使用

```php
$ret = $this->dao->findOne('user_role', array("uid" => $uid));
```

dao里封装了Medoo，自动切换读写分离，自动缓存

## 容器注册

都在public/bootstrap.php里

