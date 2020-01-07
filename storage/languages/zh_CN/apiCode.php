<?php

declare(strict_types=1);

return [

    '0' => '请求成功！',
    '-200' => '请求失败！',
    '401' => '用户未登录',
    '-201' => '处理失败',

    /* 首页 -1000 至 -2000 */
    '-1001' => 'page必须为integer',
    '-1002' => 'page必须大于等于0',
    '-1003' => 'per_page必须为integer',
    '-1004' => 'per_page必须大于等于0',
    '-1005' => 'article_category_id必须integer',
    '-1006' => 'article_platform_id必须',
    '-1007' => 'article_platform_id必须integer',
    '-1008' => 'article_tag_ids必须',
    '-1009' => 'article_tag_ids必须array',
    '-1010' => 'article_tag_ids必须在[1-3]区间',
    '-1011' => 'thumb_pic_id必须integer',
    '-1012' => 'thumb_pic_id必须',
    '-1013' => 'integral_num必须integer',
    '-1014' => 'integral_num必须在[0-200]区间',
    '-1015' => 'no_name必须为[0, 1]',
    '-1016' => '文章标题已存在',
    '-1017' => '文章不存在',
    '-1018' => '快讯不存在',
    '-1019' => 'bbs_type_id必须',
    '-1020' => 'bbs_type_id必须integer',
    '-1021' => 'type必须',
    '-1022' => 'type必须为[1,2]',
    '-1023' => '帖子标题已存在',
    '-1024' => '帖子不存在',
    '-1025' => 'resource_type必须',
    '-1026' => 'resource_type必须为[0,1,2]',
    '-1027' => 'enclosure_id必须',
    '-1028' => 'enclosure_id必须integer',
    '-1029' => '资源工具不存在',
    '-1030' => 'integral_num必须integer',
    '-1031' => 'integral_num必须在[0-100]区间',
    '-1032' => 'type必须为[0,1,2,3]中的一个',
    '-1033' => '资源工具标题已存在',
    '-1034' => 'audit_status必须为[0,1,2]中的一个',
    '-1035' => 'types必须为[1,2,3,4,5]中的一个',
    '-1036' => '文章无需积分兑换',
    '-1037' => '用户积分不足，无法兑换',
    '-1038' => '文章已兑换，无法重复兑换',
    '-1039' => '资源无需积分兑换',
    '-1040' => '用户积分不足，无法兑换',
    '-1041' => '资源已兑换，无法重复兑换',
    '-1042' => '需要邀请码才能查看',
    '-1043' => 'invitation必须string',
    '-1044' => 'invitation必须在区间[1-28]',
    '-1045' => '邀请码错误，请重新输入',
    '-1046' => 'article_page必须为integer',
    '-1047' => 'article_page必须大于等于0',
    '-1048' => 'article_per_page必须为integer',
    '-1049' => 'article_per_page必须大于等于0',
    '-1050' => 'bbs_page必须为integer',
    '-1051' => 'bbs_page必须大于等于0',
    '-1052' => 'bbs_per_page必须为integer',
    '-1053' => 'bbs_per_page必须大于等于0',
    '-1054' => 'tools_page必须为integer',
    '-1055' => 'tools_page必须大于等于0',
    '-1056' => 'tools_per_page必须为integer',
    '-1057' => 'tools_per_page必须大于等于0',
    '-1058' => 'activity_page必须为integer',
    '-1059' => 'activity_page必须大于等于0',
    '-1060' => 'activity_per_page必须为integer',
    '-1061' => 'activity_per_page必须大于等于0',
    '-1062' => 'type必须为[0,1,2]中的一个',
    '-1063' => 'file必须',
    '-1064' => 'id必须',
    '-1065' => 'id必须为array',
    '-1066' => 'id必须大于等于1',
];