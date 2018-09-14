<?php
/**
 * Created by IntelliJ IDEA.
 * User: new
 * Date: 2018-09-03
 * Time: 13:56
 */

/*
 * --------------------------------
 * 公用
 * --------------------------------
 */
$app->any('/upload', '\App\Controllers\UploadController:upload');

/*
 * --------------------------------
 * 后台
 * --------------------------------
 */
$app->group('/admin',function (){
    $this->any('/login', '\App\Controllers\Admin\LoginController:index');
    $this->any('/logout', '\App\Controllers\Admin\LoginController:logout');
    $this->any('/error/{msg}', '\App\Controllers\Admin\LoginController:error_page');
    $this->any('/index', '\App\Controllers\Admin\UserController:index');
    $this->any('/user/{action}', \App\Controllers\Admin\UserController::class);
});

/*
 * --------------------------------
 * 接口
 * --------------------------------
 */
$app->any('/api/v1/{action}', \App\Controllers\Api\V1::class);
