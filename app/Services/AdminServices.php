<?php


namespace App\Services;


use Hyperf\Cache\Annotation\Cacheable;

class AdminServices
{
    /**
     * 微服务获取用户信息 根据token
     * @Cacheable(prefix="admin_token", ttl=1800, value="_#{$token}")
     * @param $authorization
     * @return array|bool
     */
    public function getOtherUserInfo($token)
    {
        $url = Common::getOtherServerUrl(config('other_server.admin_user_info'));
        $headers = ["Authorization:$token"];
        $result = Common::httpGetRequest($url, '', $headers);
        $result = @json_decode($result, true);

        if (!isset($result['data']['userId']) || !isset($result['data']['username'])) {
            return false;
        }

        return [
            'user_id' => $result['data']['userId'] ?? 0,
            'user_name' => $result['data']['nickName'] ?? '',
        ];
    }
}