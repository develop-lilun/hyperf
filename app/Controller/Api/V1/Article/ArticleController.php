<?php

declare(strict_types=1);

namespace App\Controller\Api\V1\Article;


use App\Controller\Api\BaseController;
use App\Services\ArticleCategoryServices;
use App\Services\ArticleServices;
use App\Services\UserServices;
use App\Validates\Api\ArticleValidate;
use Hyperf\Di\Annotation\Inject;

/**
 * Class ArticleController
 * @package App\Controller\Api\V1
 */
class ArticleController extends BaseController
{
    /**
     * 文章模块验证服务
     *
     * @Inject()
     * @var ArticleValidate
     */
    protected $articleValidate;

    /**
     * @Inject()
     * @var ArticleServices
     */
    protected $articleServices;

    /**
     * @Inject()
     * @var ArticleCategoryServices
     */
    protected $articleCategoryServices;

    /**
     * 文章列表及分类数据
     * @return array
     */
    public function list()
    {

        $params = $this->request->all();
        $params['page']  = $params['page'] ?? 1;
        $params['per_page']  = $params['per_page'] ?? 20;

        // 数据验证
        $restValidate = $this->articleValidate->listValidate($params);
        if ($restValidate['status'] !== true) {
            return $this->error($restValidate['message']);
        }

        // 分类数据
        $categoryList =  $this->articleCategoryServices->listAll();

        // 列表数据
        $list  = $this->articleServices->getArticleList($params);

        // 数据返回
        $result = [
            'list' => $list['list'],
            'count' => $list['count'],
            'category_list' => $categoryList,
        ];
        return $this->success($result);
    }

    /**
     * 热门文章
     * @return array
     */
    public function hotArticleList()
    {
        $list =  $this->articleServices->getHotArticleList();
        return $this->success(['list' => $list]);
    }

    /**
     * 用户发帖
     * @return array
     */
    public function add()
    {
        $params = $this->request->all();
        // 数据验证
        $restValidate = $this->articleValidate->addValidate($params);
        if ($restValidate['status'] !== true) {
            return $this->error($restValidate['message']);
        }

        // 数据处理
        $ArticleServices = new ArticleServices();
        $result = $ArticleServices->userAddOrEditArticle($params);
        if (!$result) {
            return $this->error('-201');
        }

        return $this->success();
    }

    /**
     * 文章详细接口
     * @return array
     */
    public function info()
    {
        $params = $this->request->all();

        // 数据验证
        $restValidate = $this->articleValidate->infoValidate($params);
        if ($restValidate['status'] !== true) {
            return $this->error($restValidate['message']);
        }

        // 获取当前用户id
        $uid = UserServices::getUid();

        // 查询文章详情
        $ArticleServices = new ArticleServices();
        $info = $ArticleServices->articleInfo($params);

        // 文章详情结果
        $resultInfo = $ArticleServices->articleDataDispose($info);

        // 是否点赞收藏
        $resultInfo['is_collect'] = 0;
        $resultInfo['is_praise'] = 0;
        $resultInfo['is_buy'] = 0;
        if ($uid) {
            // 用户收藏信息
            $userCollectServices = new UserCollectServices();
            $collect = $userCollectServices->userIsCollect($uid, $resultInfo['id'], UserCollectModel::TYPE_ARTICLE);
            $resultInfo['is_collect'] = $collect > 0 ? 1 : 0;

            // 用户点赞信息
            $userPraiseServices = new UserPraiseServices();
            $praise = $userPraiseServices->userIsPraise($uid, $resultInfo['id'], UserPraiseModel::TYPE_ARTICLE);
            $resultInfo['is_praise'] = $praise > 0 ? 1 : 0;

            // 本人不需要积分查看 || 是否满足等级优势权益
            $userServices = new UserServices();
            if($resultInfo['user_id'] == $uid || $userServices->isLevelBenefit($uid, $resultInfo['user_id'])){
                $resultInfo['integral_num'] = 0;
            }
        }

        // 是否已积分兑换
        if($resultInfo['integral_num'] > 0){
            $content = $resultInfo['preview'] ?: '';
            if(isset($params['user_info']['user_id'])){
                $userIntegralLogServices = new UserIntegralLogServices();
                $isBuy = $userIntegralLogServices->issetLog(['user_id' => $uid, 'type' => UserIntegralLogModel::TYPE_DECREASE, 'reason_key' => config('integral.article_by_buy.key'), 'join_id' => $resultInfo['id']]);
                //是否兑换过
                if($isBuy > 0){
                    $content = $resultInfo['content'];
                    $resultInfo['is_buy'] = 1;
                }
            }
            $resultInfo['content'] = $content;
        }

        // 推荐文章
        $recommendArticle = $ArticleServices->recommendArticle($info['article_tag_ids']);

        // 文章浏览成功事件
        event(new UserReadAddSuccess($params['user_info']['user_id'] ?? 0, UserFootprintModel::TYPE_ARTICLE, $params['id']));

        $result = [
            'info' => $resultInfo,
            'recommend_article' => $recommendArticle,
        ];
        return $this->success($result);
    }
}