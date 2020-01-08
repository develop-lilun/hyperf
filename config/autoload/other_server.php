<?php

return [

    // 微服务base_url
    'base_url' => env('BASE_URL', 'http://192.168.0.137:8888'),

    'admin_user_info' => '/admin/current/user',                             // 后台用户信息
    'api_user_url' => '/plat/user/get/info',                                //前台用户信息调用接口
    'payment' => '/pay/order/pay',                                          //微服务支付接口
    'api_user_update_url' => '/plat/user/update/info',
    'msg_sms' => '/msg/sms',
    'msg_sms_batch' => '/msg/sms/batch',
    'user_update_status'=>'/plat/user/batch/change',	                    //修改用户状态 状态:0-禁用 1-启用 2-锁定
    'api_user_register' => '/plat/user/register',       // 用户中心注册用户接口


];