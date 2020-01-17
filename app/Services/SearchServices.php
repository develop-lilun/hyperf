<?php


namespace App\Services;


use App\Model\SearchAllModel;
use Elasticsearch\ClientBuilder;

class SearchServices
{

    public function searchAll($params)
    {
        $page = $params['page'] ?? 1;
        $perPage = $params['per_page'] ?? 20;
        $from = ($page - 1) * $perPage;  // 偏移量

        // 组装查询条件
        $query = [
            'bool' => [
                'must' => [
                    ['match' => ['content' => $params['keyword']]],
                    ['term' => ['is_disables' => 0]],
                    ['term' => ['deleted_at' => 0]]
                ],
                'should' => [
                    ['match' => [
                        'title' => [
                            'query' => $params['keyword'],
                            'boost' => 2
                        ]
                    ]]
                ]
            ]
        ];

        // 类型筛选
        if(isset($params['type'])){
            if($params['type'] == 6){
                $query['bool']['must'][] = [['terms' => ['type' => [6, 7, 8]]]];
            }else{
                $query['bool']['must'][] = [['term' => ['type' => $params['type']]]];
            }
        }

        $highlight = [
            'fields' => [
                'content' => ['type' => 'plain'],
                'title' => ['type' => 'plain']
            ]
        ];

        $searchParams = [
            'index' => 'search_all_index',
            'type' => 'search_all',
            'body' => [
                'query' => $query,
                'highlight'=> $highlight
            ],
            'size' => $perPage,
            'from' => $from,
            '_source' => ['*'],
        ];

        $clientParams = [
            'hosts' => ['http://192.168.7.31:9200'],
            'retries' => 2,
            'imNotReal' => 5
        ];
        $client = ClientBuilder::fromConfig($clientParams, true);
        $result = $client->search($searchParams);
        $list = [];
        $searchIds = [];
        if(isset($result['hits']['hits']) && $result['hits']['hits']){
            foreach ($result['hits']['hits'] as $key => $value){
                $searchIds[] = $value['_source']['id'];
                $list[] = [
                    'id' => $value['_source']['id'],
                    'type' => $value['_source']['type'],
                    'title' => $value['highlight']['title'][0] ?? $value['_source']['title'],
                    'content' => $value['highlight']['content'][0] ?? $value['_source']['content'],
                ];
            }
        }
        $searchIds = array_filter(array_unique(array_values($searchIds)));
        if($searchIds){
            $searchData = SearchAllModel::getListByIdsToKey($searchIds);
        }
        foreach ($list as $key => $value){
            $list[$key]['other_id'] = $searchData[$value['id']]['other_id'] ?? 0;
            $list[$key]['remark'] = $searchData[$value['id']]['remark'] ?? '';
            $list[$key]['other_created_at'] = $searchData[$value['id']]['other_created_at'] ?? '';
        }

        $count = $result['hits']['total']['value'] ?? 0;
        $userId = UserServices::getUid();
        // $list = $list ? $this->dataDispose($list, $userId) : $list;

        return ['list' => $list, 'count' => $count];
    }

    public function update()
    {
        $clientParams = [
            'hosts' => ['http://192.168.7.31:9200'],
            'retries' => 2,
            'imNotReal' => 5
        ];
        $client = ClientBuilder::fromConfig($clientParams, true);
        $searchParams = [
            'index' => 'search_all_index',
            'type' => 'search_all',
            'id' => '141061',
//            'body' => [
//                'doc' => [
//                    'content' => '跨境老鸟，不知道的全告诉你222'
//                ]
//            ],

        ];
        $result = $client->exists($searchParams);
        return $result;
    }

}