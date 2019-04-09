<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Cities;
use addons\cms\model\Comment;
use addons\cms\model\Coupon;
use addons\cms\model\Fabulous;
use addons\cms\model\Page;
use addons\cms\model\User;
use addons\cms\model\UserSign;
use addons\cms\model\Message;
use app\common\model\Addon;
use addons\cms\model\PrizeRecord;

use think\Db;

/**
 * 我的
 */
class My extends Base
{

    protected $noNeedLogin = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 我的页面接口
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $user_id = $this->request->post('user_id');

        if (!$user_id) {
            $this->error('缺少参数,请求失败', 'error');
        }
        //积分
        $score = $this->scores($user_id);
        $score = $score ? $score : 0;

        $sign = $this->checkSignTime($user_id, 'today');

        $sign = $sign ? 1 : 0;

        $messageCount = Message::where([
            'ismenu' => 1,
            'use_id' => ['not like', '%,' . $user_id . ',%']
        ])->count();

        //拥有优惠券数量
        $coupon = Coupon::all(function ($q) use ($user_id) {
            $q->where([
                'user_id' => ['like', '%,' . $user_id . ',%'],
                'use_id' => ['not like', '%,' . $user_id . ',%'],
            ])
                ->where('validity_datetime > :time or validity_datetime is null', ['time' => time()])
                ->field('id,user_id');
        });

        $couponCount = 0;
        if ($coupon) {
            foreach ($coupon as $k => $v) {
                $filter = array_count_values(array_filter(explode(',', $v['user_id'])));
                $couponCount += $filter[$user_id];
            }
        }

        //我的预约
        $subscribe = $this->collectionIndex($user_id, 'subscribe');
        //我的收藏
        $collections = $this->collectionIndex($user_id, 'collections');


        $this->success('请求成功', ['sign' => $sign,
            'score' => $score,
            'couponCount' => $couponCount,
            'messageCount' => $messageCount,
            'collection' => $collections,
            'subscribe' => $subscribe
        ]);
    }

    public function checkSignTime($user_id, $condition)
    {
        return UserSign::where('user_id', $user_id)
            ->whereTime('lastModifyTime', $condition)
            ->find();
    }

    /**
     * 签到接口
     * @throws \think\exception\DbException
     */
    public function signIn()
    {
        $user_id = $this->request->post('user_id');

        if (!$user_id) {
            $this->error('缺少参数,请求失败', 'error');
        }

        $user = $this->getSign($user_id);
        $signScore = intval(json_decode(Share::ConfigData(['group' => 'integral'])['value'], true)['sign']);

        if (!$user) {
            $res = UserSign::create([
                'user_id' => $user_id,
                'signcount' => 1,
                'continuitycount' => 1
            ]);

            $insert = [
                'user_sign_id' => $res->id,
                'sign_time' => $res->lastModifyTime
            ];

        } else {
            $data = [];

            //判断昨天有没有签到，得到是否连续签到
            $checkLastSign = $this->checkSignTime($user_id, 'yesterday');

            $data['continuitycount'] = $checkLastSign ? intval($user['continuitycount']) + 1 : 1;

            $data['signcount'] = intval($user['signcount']) + 1;
            $data['lastModifyTime'] = time();

            $res = UserSign::where('user_id', $user_id)
                ->update($data);

            $insert = ['user_sign_id' => $user['id'], 'sign_time' => $data['lastModifyTime']];

        }
        $insert['score'] = $signScore;
        $this->insertSignRecord($insert);
        if (!$res) {
            $this->error('签到失败');
        }

        $integral = Share::integral($user_id, $signScore);

        $integral ? $this->success('签到成功，添加积分：' . $signScore, $signScore) : $this->error('添加积分失败');

    }

    public function insertSignRecord($data)
    {
        return Db::name('cms_sign_record')
            ->insert($data);
    }

    public function scores($user_id)
    {
        return User::get(function ($query) use ($user_id) {
            $query->where('id', $user_id)->field('score');
        })['score'];
    }

    /**
     * 我的积分
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function myScore()
    {
        $user_id = $this->request->post('user_id');

        $fabulous = $this->fabulousData($user_id, 'fabulous');

        $sign = Db::name('cms_sign_record')
            ->alias('a')
            ->join('cms_user_sign b', 'a.user_sign_id = b.id')
            ->field('a.id,a.score,a.sign_time')
            ->where('b.user_id', $user_id)
            ->order('a.sign_time desc')
            ->select();


        $this->success('', [
            'integral' => [
                ['type' => 'fabulous', 'name' => '点赞', 'detailed' => $fabulous],
                ['type' => 'sign', 'name' => '签到', 'detailed' => $sign],
//                ['type'=>'share','name'=>'分享','detailed' => $share]
            ],
            'currentScore' => $this->scores($user_id)
        ]);
    }

    /**
     * 消息列表接口
     * @throws \think\exception\DbException
     */
    public function messageList()
    {
        $user_id = $this->request->post('user_id');

        if (!$user_id) {
            $this->error('缺少参数,请求失败', 'error');
        }
        $message = Message::all(function ($q) {
            $q->where('ismenu', 1)->order('createtime desc')->field('id,title,createtime,use_id');
        });

        foreach ($message as $k => $v) {
            $v['isRead'] = 0;

            if (strpos($v['use_id'], ',' . $user_id . ',') !== false) {
                $v['isRead'] = 1;
            }
            unset($v['use_id']);
        }

        $this->success('请求成功', ['messageList' => $message]);
    }

    /**
     * 消息详情接口
     * @throws \think\exception\DbException
     */
    public function messageDetails()
    {
        $user_id = $this->request->post('user_id');
        $message_id = $this->request->post('message_id');
        $isRead = $this->request->post('isRead') ? 1 : 2;
        if (!$user_id || !$message_id || !$isRead) {
            $this->error('缺少参数,请求失败', 'error');
        }
        $message = Message::get(function ($q) use ($message_id) {
            $q->where('id', $message_id)->field('id,title,content,analysis,createtime,use_id');
        });

        if ($isRead == 2) {
            $updateUses = $message['use_id'] == '' ? ',' . $user_id . ',' : $message['use_id'] . $user_id . ',';

            //如果未读,更新为已读
            Message::where('id', $message_id)->setField('use_id', $updateUses);
        }
        unset($message['use_id']);

        $this->success('请求成功', ['messageDetails' => $message]);
    }

    /**
     * 点赞数据
     * @param $user_id       用户ID
     * @param $type          类型
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fabulousData($user_id, $type)
    {
        return Fabulous::field('id,score,fabuloustime')
            ->where([
                'user_id' => $user_id,
                'type' => $type
            ])
            ->order('fabuloustime desc')
            ->select();
    }

    public function getSign($user_id)
    {
        return UserSign::get(function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });
    }

    /**
     * 得到收藏或者预约
     * @param $user_id       用户ID
     * @param $table         关联表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function collectionIndex($user_id, $table)
    {
        $info = Cities::field('id,cities_name')
            ->with(['storeList' => function ($q) use ($user_id, $table) {
                $q->with(['planacarIndex' => function ($query) use ($user_id, $table) {
                    $query->order('weigh desc')->with(['models' => function ($models) {
                        $models->withField('id,name,brand_id,price,models_name');
                    }, $table => function ($collections) use ($user_id) {
                        $collections->where('user_id', $user_id)->withField('id');
                    }]);
                }, 'usedcarCount' => function ($query) use ($user_id, $table) {
                    $query->order('weigh desc')->with(['models' => function ($models) {
                        $models->withField('id,name,brand_id,price,models_name');
                    }, $table => function ($collections) use ($user_id) {
                        $collections->where('user_id', $user_id)->withField('id');
                    }]);
                }, 'logisticsCount' => function ($query) use ($user_id, $table) {
                    $query->with(['models' => function ($models) {
                        $models->withField('id,name,brand_id,price,models_name');
                    }, $table => function ($collections) use ($user_id) {
                        $collections->where('user_id', $user_id)->withField('id');
                    }]);
                }, 'rentalmodelsinfo' => function ($query) use ($user_id, $table) {
                    $query->with(['models' => function ($models) {
                        $models->withField('id,name,brand_id,price,models_name');
                    }, $table => function ($collections) use ($user_id) {
                        $collections->where('user_id', $user_id)->withField('id');
                    }]);
                }]);
            }])->select();
        $newCollect = [];
        $usdCollect = [];
        $logisticsCollect = [];
        $rentCollect = [];
        foreach ($info as $k => $v) {
            if (!$v['store_list']) {
                unset($info[$k]);
                continue;
            }
            foreach ($v['store_list'] as $key => $value) {
                if (!$value['planacar_index'] && !$value['usedcar_count'] && !$value['logistics_count']) {
                    continue;
                }

                if ($value['planacar_index']) {
                    foreach ($value['planacar_index'] as $kk => $vv) {
                        $vv['models']['name'] = $vv['models']['name'] . ' ' . $vv['models']['models_name'];
                        $vv['city'] = ['id' => $v['id'], 'cities_name' => $v['cities_name']];
                        unset($vv['recommendismenu'], $vv['specialismenu'], $vv['specialimages'], $vv['store_id'], $vv['models']['models_name']);
                        $newCollect[] = $vv;
                    }
                }

                if ($value['usedcar_count']) {
                    foreach ($value['usedcar_count'] as $kk => $vv) {
                        $vv['models']['name'] = $vv['models']['name'] . ' ' . $vv['models']['models_name'];
                        $vv['city'] = ['id' => $v['id'], 'cities_name' => $v['cities_name']];
                        unset($vv['store_id'], $vv['models']['models_name']);
                        $usdCollect[] = $vv;
                    }
                }

                if ($value['logistics_count']) {
                    foreach ($value['logistics_count'] as $kk => $vv) {
                        $vv['models']['name'] = $vv['models']['name'] . ' ' . $vv['models']['models_name'];
                        $vv['city'] = ['id' => $v['id'], 'cities_name' => $v['cities_name']];
                        unset($vv['store_id'], $vv['brand_id'], $vv['models']['models_name']);
                        $logisticsCollect[] = $vv;
                    }
                }

                if ($value['rentalmodelsinfo']) {
                    foreach ($value['rentalmodelsinfo'] as $kk => $vv) {
                        $vv['models']['name'] = $vv['models']['name'] . ' ' . $vv['models']['models_name'];
                        $vv['city'] = ['id' => $v['id'], 'cities_name' => $v['cities_name']];
                        unset($vv['store_id'], $vv['brand_id'], $vv['models']['models_name']);
                        $rentCollect[] = $vv;
                    }
                }
            }

        }
        return ['carSelectList' => [
            [
                'type' => 'new',
                'type_name' => '新车',
                'planList' => $newCollect ? $this->arraySort($newCollect, $table) : $newCollect
            ],
            [
                'type' => 'used',
                'type_name' => '二手车',
                'planList' => $usdCollect ? $this->arraySort($usdCollect, $table) : $usdCollect
            ],
            [
                'type' => 'logistics',
                'type_name' => '新能源车',
                'planList' => $logisticsCollect ? $this->arraySort($logisticsCollect, $table) : $logisticsCollect
            ],
            [
                'type' => 'rent',
                'type_name' => '租车',
                'planList' => $rentCollect ? $this->arraySort($rentCollect, $table) : $rentCollect
            ]
        ]];
    }

    /**
     *数组根据收藏ID排序
     * @param $arrays
     * @return array
     */
    public function arraySort($arrays, $tables)
    {
        $arr = array();
        foreach ($arrays as $k => $v) {
            $arr[] = $v[$tables]['id'];
        }
        rsort($arr);
        $newArr = [];
        foreach ($arr as $v) {
            foreach ($arrays as $key => $value) {
                if ($value[$tables]['id'] == $v) {
                    $newArr[] = $value;
                }
            }
        }

        return $newArr;
    }


    /**
     * 优惠券详情接口
     */
    public function coupons()
    {
        $user_id = $this->request->post('user_id');
        $time = time();

        //未使用
        $notUsed = $this->getCoupon($user_id, [
//            'validity_datetime' => ['GT', $time],
            'user_id' => ['like', '%,' . $user_id . ',%'],
            'use_id' => ['not like', '%,' . $user_id . ',%']
        ], 'validity_datetime > ' . $time . ' or validity_datetime is null');
        //已使用
        $used = $this->getCoupon($user_id, [
            'user_id&use_id' => ['like', '%,' . $user_id . ',%'],
        ]);

        //已过期
        $overdues = $this->getCoupon($user_id, [
            'user_id' => ['like', '%,' . $user_id . ',%'],
            'use_id' => ['not like', '%,' . $user_id . ',%'],
            'validity_datetime' => ['LT', $time],
            'validity_datetime' => ['neq', 'null']
        ]);

        $this->success('请求成功', ['coupons' => [
            'notUsed' => $notUsed,
            'used' => $used,
            'overdues' => $overdues
        ]]);
    }

    /**
     * 得到满足条件的优惠券信息
     * @param $user_id        用户ID
     * @param $where          查询条件
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCoupon($user_id, $where, $else = null)
    {
        $coupons = Coupon::where($where)
            ->where($else ? $else : '')
            ->order('validity_datetime')
            ->field('id,coupon_name,display_diagramimages,coupon_amount,threshold,models_ids,createtime,
            validity_datetime,user_id')
            ->select();

        foreach ($coupons as $k => $v) {
            $filter = array_count_values(array_filter(explode(',', $v['user_id'])));
            $v['coupon_count'] = $filter[$user_id];
            unset($v['user_id']);
        }

        return $coupons;
    }

    /**
     * Notes:  我的奖品列表
     * User: glen9
     * Date: 2018/12/22
     * Time: 15:56
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  function prizeList(){
        $user_id = $this->request->post('user_id');
        if(!$user_id) $this->error('缺少参数或参数错误','error');
        $prizeData = PrizeRecord::with(['prizeData'=>function($q){
            $q->withField(['id','prize_name','prize_image','rules','city_id'])->with(['cityName'=>function(){

            }]);
        }])->where('user_id',$user_id)->order(['is_use','id'=> 'desc'])->select();
        $this->success('请求成功',$prizeData);
    }
    /**
     * 我发表的评论
     */
    public function comment()
    {
        $page = (int)$this->request->request('page');
        $commentList = Comment::
        with('archives')
            ->where(['user_id' => $this->auth->id])
            ->order('id desc')
            ->page($page, 10)
            ->select();
        foreach ($commentList as $index => $item) {
            $item->create_date = human_date($item->createtime);
        }

        $this->success('', ['commentList' => $commentList]);
    }

    /**
     * 关于我们
     */
    public function aboutus()
    {

        $pageInfo = Page::getByDiyname('aboutus');
        if (!$pageInfo || $pageInfo['status'] == 'hidden') {
            $this->error(__('单页未找到'));
        }
        $this->success('', ['pageInfo' => $pageInfo]);
    }
}
