<?php


namespace App\Services;


use App\Model\UsersModel;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class UserServices
{
    /**
     * @Inject()
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @Inject()
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * 用户信息
     * array $ids [1,2]
     * @return array
     */
    public function getUserList($ids, $field = ['user_id', 'nick', 'avatar'])
    {

        $list = UsersModel::getListByIds($ids, $field);
        $result = [];
        foreach ($list as $key => $value) {
            $result[$value['user_id']] = $value;
            $result[$value['user_id']]['face_url'] = $value['avatar'] ?? '';
            $result[$value['user_id']]['user_name'] = $value['nick'] ?? '';
        }
        return $result;
    }

    /**
     * 用户信息单个用户
     * array $id 用户id
     * @return array
     */
    public function getUserInfoById($id)
    {
        $info = UsersModel::getListByIds($id, ['user_id', 'nick', 'avatar']);

        //返回的数据格式
        $userInfo = [
            'user_id' => $info['user_id'],
            'user_name' => $info['nick'],
            'face_url' => $info['avatar']
        ];
        return $userInfo;
    }

    /**
     * 获取微服务单个用户信息
     *
     * @Cacheable(prefix="get_user_info", ttl=1800, value="_#{sid}", listener="user-update")
     * @param string $accessToken
     * @param string $sid
     * @return bool|mixed
     */
    public function getUserInfo($accessToken = '', $sid = '')
    {
        if (!$accessToken || !$sid) {
            return false;
        }

        $result = $this->getOriginUserInfo($accessToken, $sid);
        $result = @json_decode($result, true);

        if (!is_array($result) || !$result || !isset($result['code']) || $result['code'] != 0 || !isset($result['data']['userInfo'])) {
            return false;
        }

        $userInfo = $result['data']['userInfo'];

        // 新用户使用默认地址默认头像地址 和昵称
        $userInfo['avatar'] = $userInfo['avatar'] ?: config('app.user_default_avatar');
        $userInfo['nickname'] = $userInfo['nickname'] ?: Common::defaultNick();

        // 同步用户信息
        $syncResult = $this->userInfoSync($userInfo);
        if (!$syncResult) {
            return false;
        }

        return $userInfo;
    }

    public function flushCache($sid)
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent('user-update', [$sid]));

        return true;
    }

    /**
     * 同步用户信息
     * @param $userInfo
     * @return bool
     */
    public function userInfoSync($userInfo)
    {
        $count = UsersModel::getCount(['user_id' => $userInfo['userId']]);

        $userData['user_id'] = $userInfo['userId'];
        $userData['mobile'] = $userInfo['mobile'] ?? '';
        $userData['nick'] = $userInfo['nickname'] ?? '';
        $userData['email'] = $userInfo['email'] ?? '';
        $userData['avatar'] = $userInfo['avatar'] ?? '';
        $userData['created_at'] = $userInfo['createTime'] ?? '';

        // 本地存在则修改修改用户信息 不存在则新增用户信息
        if ($count) {
            unset($userData['user_id']);
            if (!$this->updateUserInfo(['user_id' => $userInfo['userId']], $userData)) {
                return false;
            }
        } else {
            if (!$this->addUserInfo($userData)) {
                return false;
            }
        }

        return true;
    }


    /**
     * 更新用户缓存
     * @param null $sessionId
     * @param array $userData
     * @return bool
     */
    public function updateUserCache($sessionId = null, $userData = [])
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent('user-update', [$sessionId]));

        return true;
    }

    /**
     * 获取用户中心的用户数据
     *
     * @param string $accessToken
     * @param string $sid
     *
     * @return bool|mixed
     */
    public function getOriginUserInfo($accessToken = '', $sid = '')
    {
        $url = Common::getOtherServerUrl(config('services_config.api_user_url'));
        $header = ["token:$accessToken", "sessionId:$sid"];
        try {
            $result = Common::httpPostRequest($url, [], $header);
            if (!$result) {
                return false;
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    /**
     * 获取当前客户的用户登录信息
     * @return mixed|null
     */
    public static function getClientUserInfo()
    {
        return Context::get('user_info', []);
    }


    /**
     * 获取当前客户的用户uid
     * @return int
     */
    public static function getUid()
    {
        return self::getClientUserInfo()['user_id'] ?? 0;
    }


    /**
     * 用户状态
     * @param $uid
     * @return bool|\Carbon\CarbonInterface|float|int|mixed|string
     */
    public static function userStatus($uid)
    {
        return UsersModel::getValueByWhere(['user_id' => $uid], 'status') ?? false;
    }

    /**
     * 用户创造数
     *
     * @param $userId
     *
     * @return array
     */
    public function UserCreationCount($userId)
    {
        $where = ['user_id' => $userId];
        $articleModel = new ArticleModel();
        $bbsModel = new BbsModel();
        $encyclopediaModel = new EncyclopediaDraftModel();
        $articleCount = $articleModel->getCount($where);
        $encyclopediaCount = $encyclopediaModel->getCount($where);
        $questionCount = $bbsModel->getCount(array_merge($where, ['type' => 1]));
        $ideaCount = $bbsModel->getCount(array_merge($where, ['type' => 2]));

        $result = [
            'article_count' => $articleCount,
            'encyclopedia_count' => $encyclopediaCount,
            'question_count' => $questionCount,
            'idea_count' => $ideaCount,
        ];

        return $result;
    }

    /**
     * 更新用户信息
     *
     * @param array $where
     * @param       $userData
     *
     * @return bool
     */
    public function updateUserInfo($where = [], $userData = [])
    {
        try {
            return UsersModel::query()->where($where)->update($userData);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage() . '用户信息更新失败');
            return false;
        }
    }

    /**
     *
     * @param array $where
     * @param       $userData
     *
     * @return bool
     */
    public function addUserInfo($userData)
    {
        try {
            $u = UsersModel::query()->where(['mobile' => $userData['mobile']])->count();
            if ($u >= 1) {
                return true;
            } else {
                UsersModel::query()->insert($userData);

                return true;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage() . '用户信息添加失败');

            return false;
        }

    }


    /**
     * 更新登录天数
     * @param null $userId
     * @return bool|int
     */
    public function updateLoginDay($userId = null)
    {
        try {
            return UsersModel::query()->where(['user_id' => $userId])->increment('login_day');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    /**
     * 更新article_num
     * @param null $userId
     * @return bool|int
     */
    public function updateArticleNum($userId = null)
    {
        try {
            return UsersModel::query()->where(['user_id' => $userId])->increment('article_num');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    /**
     * 更新fan_num
     * @param null $userId
     * @param int $type
     * @return bool|int
     */
    public function updateFansNum($userId = null, $type = 1)
    {
        if ($type == 1) {
            try {
                return UsersModel::query()->where(['user_id' => $userId])->increment('fans_num');
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
        } else {
            try {
                return UsersModel::query()->where(['user_id' => $userId])->decrement('fans_num');
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
        }
    }


    /**
     * 查询两用户是否满足等级优势权益
     * @param $userId
     * @param $byUserId
     * @return bool
     */
    public function isLevelBenefit($userId, $byUserId)
    {
        if ($userId && $byUserId) {
            $userData = $this->getUserList([$userId, $byUserId], ['user_id', 'level']);
            if (isset($userData[$userId]['level']) && isset($userData[$byUserId]['level']) && $userData[$userId]['level'] > $userData[$byUserId]['level']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 根据手机号码获取用户信息
     * array $mobile [xxx,xxx]
     * @return array
     */
    public function getUserListByMobile($mobiles, $field = ['user_id', 'mobile', 'nick', 'avatar'])
    {
        $list = UsersModel::getListByWhereIn('mobile', $mobiles, $field);
        $result = [];
        foreach ($list as $key => $value) {
            $result[$value['mobile']] = $value;
            $result[$value['mobile']]['face_url'] = $value['avatar'] ?? '';
            $result[$value['mobile']]['user_name'] = $value['nick'] ?? '';
        }
        return $result;
    }
}