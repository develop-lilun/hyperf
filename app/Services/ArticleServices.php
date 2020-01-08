<?php

namespace App\Services;

use App\Model\ArticleModel;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class ArticleServices
{
    /**
     * 文章标签服务层
     * @Inject()
     * @var ArticleTagServices
     */
    protected $articleTagServices;

    /**
     * 文章分类服务层
     * @Inject()
     * @var ArticleCategoryServices
     */
    protected $articleCategoryServices;

    /**
     * 文章平台服务层
     * @Inject()
     * @var ArticlePlatformServices
     */
    protected $articlePlatformService;

    /**
     * 用户信息服务层
     * @Inject()
     * @var UserServices
     */
    protected $userServices;

    /**
     * 文件信息服务层
     * @Inject()
     * @var UploadService
     */
    protected $uploadServices;


    /**
     * 查询文章数据
     *
     * @param $params
     *
     * @return array
     */
    public function getList($params, $field = [])
    {

        // 条件数据
        $queryDate = Common::arrayOnly($params,
            ['article_category_id', 'user_id', 'article_platform_id', 'is_disables', 'audit_status', 'push_platform']);

        // 其他特定条件
        $queryRaw = '';
        if (isset($params['article_tag_id'])) {
            $queryRaw = "FIND_IN_SET(" . $params['article_tag_id'] . ", `article_tag_ids`)";
        }
        if (isset($params['title'])) {
            $queryDate[] = ['title', 'like', '%' . $params['title'] . '%'];
        }
        if (isset($params['author'])) {
            $queryDate[] = ['author', 'like', '%' . $params['author'] . '%'];
        }
        if (isset($params['start_time'])) {
            $queryDate[] = ['created_at', '>=', $params['start_time']];
        }
        if (isset($params['end_time'])) {
            $queryDate[] = ['created_at', '<=', $params['end_time']];
        }
        if (isset($params['remark'])) {
            $queryDate[] = ['remark', 'like', '%' . ['remark'] . '%'];
        }

        // 排序数据
        $sort = $params['sort'] ?? 'created_at';
        $order = $params['order'] ?? 'desc';

        // 列表
        $articleModel = new ArticleModel();
        $count = $articleModel->getCount($queryDate, $queryRaw);

        // 分页数据
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 20;
        $perPage = $perPage == 0 ? $count : $perPage;

        $list = $articleModel->getList($queryDate, $page, $perPage, $sort, $order, $field, $queryRaw);
        $list = $this->dataDispose($list);
        // 总数
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 新增或修改
     *
     * @param $params
     *
     * @return mixed
     */
    public function addEdit($params)
    {
        // 数据组装
        $field = [
            'title',
            'seo_title',
            'seo_keywords',
            'description',
            'preview',
            'content',
            'article_category_id',
            'article_platform_id',
            'no_name',
            'thumb_pic_id',
            'praise_num',
            'comment_num',
            'read_num',
            'collect_num',
            'share_num',
            'invitation',
            'integral_num',
            'is_disables',
            'push_time'
        ];
        $data = Common::arrayOnly($params, $field);

        if (isset($params['article_tag_ids'])) {
            $data['article_tag_ids'] = join(',', $params['article_tag_ids']);
        }
        if (isset($params['push_time'])) {
            $data['push_time'] = $params['push_time'] == '' ? null : $params['push_time'];
        }

        $ArticleModel = new ArticleModel();
        if (isset($params['id']) && $params['id'] !== 0) {
            // 修改且审核
            Db::beginTransaction();
            try {
                // 如果存在审核
                if (isset($params['audit_status']) && ($params['audit_status'] == 1 || $params['audit_status'] == 2)) {
                    $data['audit_status'] = $params['audit_status'];

                    // 添加审核记录
                    $auditData = [
                        'article_id' => $params['id'],
                        'admin_id' => $params['user_info']['user_id'],
                        'status' => $params['audit_status'] == 1 ? 1 : 0,
                        'reason' => $params['reason'] ?? '',
                        'remark' => $params['remark'] ?? '',
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    ArticleAuditModel::insert($auditData);

                }
                if (isset($data['push_time'])) {
                    $data['created_at'] = $data['push_time'];
                    $data['updated_at'] = $data['push_time'];
                }

                $result = ArticleModel::where(['id' => $params['id']])->update($data);
//                if(isset($params['audit_status']) && $params['audit_status'] == 1){
//                    event(new ArticlePublishSuccess($params['id']));
//                }

                Db::commit();
                return true;
            } catch (\Exception $e) {
                Db::rollBack();
                return false;
            }
        } else {
            // 新增
            $userVip = config('app.user_vip');
            $data['user_id'] = $userVip['user_id'];
            $data['author'] = $params['user_info']['user_name'];
            $data['admin_id'] = $params['user_info']['user_id'];
            $data['audit_status'] = 3;
            $data['push_platform'] = 2;
            $data['created_at'] = $data['push_time'] ?? date('Y-m-d H:i:s');
            $data['updated_at'] = $data['push_time'] ?? date('Y-m-d H:i:s');
            $result = ArticleModel::insert($data);
        }

        return $result;
    }

    /**
     * 删除数据
     *
     * @param $ids
     */
    public function delete($ids)
    {
        $ArticleModel = new ArticleModel();
        return ArticleModel::dropIds($ids);
    }

    /**
     * 根据id 查询详情
     *
     * @param $id
     *
     * @return mixed
     */
    public function info($id, $field = ['*'])
    {
        $ArticleModel = new ArticleModel();
        $result = ArticleModel::where(['id' => $id])->first($field)->toArray();
        // 处理主图显示
        if ($result['thumb_pic_id']) {
            $uploadFile = new UploadService();
            $url = $uploadFile->getFileIdToUrl($result['thumb_pic_id']);
        }
        $result['push_time'] = is_null($result['push_time']) ? '' : $result['push_time'];
        $result['thumb_pic_url'] = $url ?? '';
        $result['article_tag_ids'] = explode(',', $result['article_tag_ids']);

        return $result;
    }

    /**
     * 首页文章数据处理
     *
     * @Cacheable(prefix="get_article_list", ttl=60, value="_#{params.page}_#{params.per_page}_#{params.article_category_id}_#{params.article_tag_id}", listener="article-update")
     * @param $params
     * @param $field
     *
     * @return array
     */
    public function getArticleList($params)
    {
        // 条件数据
        $queryDate = Common::arrayOnly($params, ['article_category_id']);
        $queryDate['is_disables'] = 0; // 未禁用
        $time = date('Y-m-d H:i:s');
        $whereRaw = "audit_status in(1, 3) AND (push_time is NULL or push_time <= '$time') ";

        if (isset($params['article_tag_id'])) {
            $whereRaw .= " AND FIND_IN_SET(" . $params['article_tag_id'] . ", `article_tag_ids`)";
        }

        // 分页数据
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 20;

        // 排序数据
        $sort = $params['sort'] ?? 'updated_at';
        $order = $params['order'] ?? 'desc';

        // 列表
        $field = [
            'id',
            'title',
            'description',
            'article_category_id',
            'push_platform',
            'user_id',
            'thumb_pic_id',
            'no_name',
            'read_num',
            'praise_num',
            'updated_at as created_at',
            'push_time'
        ];
        $list = ArticleModel::getList($queryDate, $page, $perPage, $sort, $order, $field, $whereRaw);

        // 数据处理
        $list = $this->dataDispose($list, 1);

        // 总数
        $count = ArticleModel::getCount($queryDate, $whereRaw);
        return ['list' => $list, 'count' => $count];
    }

    /**
     * 热门文章数据
     *
     * @Cacheable(prefix="get_hot_article_list", ttl=300)
     * @return array
     */
    public function getHotArticleList()
    {
        $day = config('app.article_hot_list_day', 5);
        $where = [
            ['is_disables', '=', 0],// 未禁用
            ['created_at', '>=', date('Y-m-d', strtotime("-$day day"))] // 3天内的
        ];
        $time = date('Y-m-d H:i:s');
        $whereRaw = "audit_status in(1, 3) AND (push_time is NULL or push_time <= '$time') ";
        $field = ['id', 'title', 'description', 'thumb_pic_id', 'read_num', 'created_at', 'push_time'];
        $list = ArticleModel::getList($where, 1, 5, 'read_num', 'desc', $field, $whereRaw);
        $list = $this->dataDispose($list, 1);
        return $list;
    }

    /**
     * 数据处理
     *
     * @param array $data
     *
     * @return array
     */
    public function dataDispose($data = [], $isTimeChange = 0)
    {
        // 标签 平台 分类ids 主图搜集
        $tagIdsArray = [];
        $categoryIdsArray = [];
        $platformIdsArray = [];
        $picIdsArray = [];
        $userIdsArray = [];
        foreach ($data as $key => $value) {
            if (isset($value['article_tag_ids']) && $value['article_tag_ids']) {
                $tagIdsArray = array_merge($tagIdsArray, explode(',', $value['article_tag_ids']));
            }

            if (isset($value['article_category_id']) && $value['article_category_id']) {
                $categoryIdsArray[] = $value['article_category_id'];
            }

            if (isset($value['article_platform_id']) && $value['article_platform_id']) {
                $platformIdsArray[] = $value['article_platform_id'];
            }

            if (isset($value['thumb_pic_id']) && $value['thumb_pic_id']) {
                $picIdsArray[] = $value['thumb_pic_id'];
            }
            if (isset($value['user_id']) && $value['user_id']) {
                $userIdsArray[] = $value['user_id'];
            }

        }
        $tagIdsArray = array_filter(array_unique(array_values($tagIdsArray)));
        $categoryIdsArray = array_filter(array_unique(array_values($categoryIdsArray)));
        $platformIdsArray = array_filter(array_unique(array_values($platformIdsArray)));
        $picIdsArray = array_filter(array_unique(array_values($picIdsArray)));
        $userIdsArray = array_filter(array_unique(array_values($userIdsArray)));

        // 根据标签 平台 分类ids查询信息
        $tagData = [];
        $categoryData = [];
        $platformData = [];
        $picData = [];
        $userData = [];
        if ($tagIdsArray) {
            $tagData = $this->articleTagServices->getTagNameByIds($tagIdsArray);
        }
        if ($categoryIdsArray) {
            $categoryData = $this->articleCategoryServices->getCategoryNameByIds($categoryIdsArray);
        }
        if ($platformIdsArray) {
            $platformData = $this->articlePlatformService->getPlatformNameByIds($platformIdsArray);
        }
        if ($picIdsArray) {
            $picData = $this->uploadServices->getFileIdsToUrl($picIdsArray);
        }
        if ($userIdsArray) {
            $userData = $this->userServices->getUserList($userIdsArray);
        }

        // 将标签 平台 分类查询信息 组装返回
        foreach ($data as $key => $value) {
            if (isset($value['article_tag_ids'])) {
                $data[$key]['article_tag_ids'] = $value['article_tag_ids'] ? explode(',',
                    $value['article_tag_ids']) : [];
                $tagNameArray = [];
                foreach ($data[$key]['article_tag_ids'] as $k => $v) {
                    $tagNameArray[] = $tagData[$v] ?? '';
                }

                $data[$key]['article_tag_names'] = join(',', array_filter(array_unique(array_values($tagNameArray))));
            }
            if (isset($value['article_category_id'])) {
                $data[$key]['article_category_name'] = $categoryData[$value['article_category_id']] ?? '';
            }
            if (isset($value['article_platform_id'])) {
                $data[$key]['article_platform_name'] = $platformData[$value['article_platform_id']] ?? '';
            }
            if (isset($value['thumb_pic_id'])) {
                $data[$key]['thumb_pic_url'] = $picData[$value['thumb_pic_id']] ?? '';
            }
            if (isset($value['user_id']) && $value['user_id']) {
                if (isset($value['no_name']) && $value['no_name'] == 1) {
                    $data[$key]['user_face_url'] = '';
                    $data[$key]['user_name'] = '匿名';
                    $data[$key]['user_id'] = 0;

                } else {
                    $data[$key]['user_face_url'] = $userData[$value['user_id']]['face_url'] ?? '';
                    $data[$key]['user_name'] = $userData[$value['user_id']]['user_name'] ?? '';
                }
            }
            if ($isTimeChange == 1 && isset($value['created_at'])) {
                $value['created_at'] = isset($value['push_time']) && $value['push_time'] ? $value['push_time'] : $value['created_at'];
                $data[$key]['created_at'] = Common::timeChange($value['created_at']);
            }
        }

        return $data;
    }

    /**
     * 用户发帖处理
     *
     * @param $params
     */
    public function userAddOrEditArticle($params)
    {
        $field = ['title', 'content', 'article_platform_id', 'no_name', 'thumb_pic_id', 'integral_num'];
        $data = Common::arrayOnly($params, $field);

        $data['seo_title'] = $data['title'] ?? '';
        $data['description'] = Common::msUbStr(Common::clearTags($data['content']), 200);
        $data['audit_status'] = 0;

        if (isset($params['article_tag_ids'])) {
            $tagData = $this->articleTagServices->getTagNameByIds($params['article_tag_ids']);
            $data['seo_keywords'] = join(',', $tagData);
        }
        if (isset($params['id']) && $params['id']) {
            $result = ArticleModel::query()->where(['id' => $params['id']])->update($data);
        } else {
            $data['user_id'] = $params['user_info']['user_id'];
            $data['author'] = $params['user_info']['user_name'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = ArticleModel::query()->insertGetId($data);
        }
        return $result;
    }

    /**
     * 查询文章信息
     *
     * @param $params
     *
     * @return mixed
     */
    public function articleInfo($params)
    {
        // 获取信息
        $filed = [
            'id',
            'title',
            'seo_title',
            'article_tag_ids',
            'seo_keywords',
            'description',
            'preview',
            'content',
            'user_id',
            'no_name',
            'praise_num',
            'comment_num',
            'read_num',
            'collect_num',
            'integral_num',
            'created_at',
            'push_time'
        ];
        $result = ArticleModel::query()->where(['id' => $params['id']])->first($filed)->toArray();

        return $result;
    }

    /**
     * 处理文章详情显示前的数据
     *
     * @param array $info
     *
     * @return array|mixed
     */
    public function articleDataDispose($info = [])
    {
        unset($info['article_tag_ids']);
        // 数据处理
        $info = $this->dataDispose([$info], 1)[0];
        $info['seo_keywords'] = $info['seo_keywords'] ?: '';
        $info['description'] = $info['description'] ?: '';
        $info['created_at'] = isset($info['push_time']) && $info['push_time'] ? $info['push_time'] : $info['created_at'];

        return $info;
    }

    /**
     * 文章相关推荐
     *
     * @param $articleTagIds
     *
     * @return array
     */
    public function recommendArticle($articleTagIds)
    {
        $field = ['id', 'title', 'user_id', 'no_name', 'thumb_pic_id', 'read_num', 'created_at', 'push_time'];
        $time = date('Y-m-d H:i:s');
        $startTime = date('Y-m-d', strtotime("-29 day"));  // 取最近一个月的数据
        $whereRaw = " audit_status in(1, 3) AND ((push_time is NULL AND  created_at > '$startTime') OR (push_time > '$startTime' AND push_time <= '$time'))";
        $articleTagArray = array_filter(explode(',', $articleTagIds));
        foreach ($articleTagArray as $key => $value) {
            if ($key == 0){
                $whereRaw .= ' AND (';
            }
            if ($key > 0) {
                $whereRaw .= ' OR ';
            }
            $whereRaw .= "FIND_IN_SET($value, `article_tag_ids`)";
            if($key == count($articleTagArray) - 1){
                $whereRaw .= ')';
            }
        }

        $list = ArticleModel::getList(['is_disables' => 0], 1, 6, 'read_num', 'desc', $field, $whereRaw);
        $list = $this->dataDispose($list, 1);
        return $list;
    }

    /**
     * 根据id 返回 信息
     * @param array $ids
     * @return  'id' => 'value'
     */
    public function getListByIds($ids = [])
    {
        $field = ['id', 'title', 'description', 'user_id', 'no_name', 'thumb_pic_id', 'read_num', 'collect_num'];
        $list = ArticleModel::query()->whereIn('id', $ids)->where(['is_disables' => 0])->get($field)->toArray();
        $list = $this->dataDispose($list);
        $result = [];
        foreach ($list as $value) {
            $result[$value['id']] = $value;
        }
        return $result;
    }

    /**
     * 根据id 返回 信息
     * @param array $ids
     * @return  'id' => 'value'
     */
    public function getTitleByIds($ids = [])
    {
        $field = ['id', 'title', 'read_num', 'praise_num'];
        $list = ArticleModel::query()->whereIn('id', $ids)->where(['is_disables' => 0])->get($field)->toArray();
        return array_column($list, null, 'id');
    }

    /**
     * 处理收藏量和阅读量实时数据
     * @param $list
     * @return mixed
     */
    public function HomeDataDispose($list)
    {
        $articleIds = [];
        foreach ($list as $value) {
            $articleIds[] = $value['id'];
        }
        $resultData = $this->getTitleByIds($articleIds);

        foreach ($list as $key => $value) {
            $list[$key]['read_num'] = $resultData[$value['id']]['read_num'] ?? 0;
            $list[$key]['praise_num'] = $resultData[$value['id']]['praise_num'] ?? 0;
        }

        return $list;
    }


}

