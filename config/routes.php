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
use App\Middleware\AdminVerifyPeremptoryMiddleware;
use App\Middleware\UserVerifyPeremptoryMiddleware;
use App\Middleware\UserVerifyMiddleware;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');

// 后台接口-需要登录
Router::addGroup('/admin/', function () {
    Router::post('v1/user/foo', [\App\Controller\Admin\V1\UserController::class, 'foo']);
    Router::post('v1/user/ccc', [\App\Controller\Admin\V1\UserController::class, 'ccc']);


}, ['middleware' => [AdminVerifyPeremptoryMiddleware::class]]);

// 后台接口-无需登录
Router::addGroup('/admin/', function () {


});

// 前台接口-需要登录
Router::addGroup('/api/', function () {



}, ['middleware' => [UserVerifyPeremptoryMiddleware::class]]);

// 前台接口-无需登录
Router::addGroup('/api/', function () {

    // 文章
    Router::get('v1/article/list', [\App\Controller\Api\V1\Article\ArticleController::class, 'list']);
    Router::get('v1/article/hot_list', [\App\Controller\Api\V1\Article\ArticleController::class, 'hotArticleList']);
    Router::get('v1/article/info', [\App\Controller\Api\V1\Article\ArticleController::class, 'info']);
    Router::get('v1/article_platform/list_all', [\App\Controller\Api\V1\Article\ArticleController::class, 'articlePlatformListAll']);
    Router::get('v1/article_tag/list_all', [\App\Controller\Api\V1\Article\ArticleController::class, 'articleTagListAll']);
    Router::addRoute(['get', 'post'], 'v1/article/capture_sync', [\App\Controller\Api\V1\Article\ArticleController::class, 'captureArticleSync']);

}, ['middleware' => [UserVerifyMiddleware::class]]);

