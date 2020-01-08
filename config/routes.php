<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

Router::addGroup('/admin/', function () {
    Router::post('v1/user/foo', [\App\Controller\Admin\V1\UserController::class,'foo']);
    Router::post('v1/user/ccc', [\App\Controller\Admin\V1\UserController::class, 'ccc']);


}, ['middleware' => []]);

Router::addGroup('/api/', function () {

    Router::get('v1/article/list', [\App\Controller\Api\V1\ArticleController::class, 'list']);
    Router::get('v1/article/hot_list', [\App\Controller\Api\V1\ArticleController::class, 'hotArticleList']);




});
