<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Archives;
use addons\cms\model\Block;
use addons\cms\model\Channel;
use app\common\model\Addon;
use think\Config as ThinkConfig;
use fast\Random;
use think\Request;
use Upyun\Upyun;
use Upyun\Config;

/**
 * 首页
 */
class Index extends Base
{
    protected $noNeedLogin = '*';

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * 首页
     */
    public function index()
    {
        $bannerList = [];
        $list = Block::getBlockList(['name' => 'indexfocus', 'row' => 5]);
        foreach ($list as $index => $item) {
            $bannerList[] = ['image' => cdnurl($item['image'], true), 'url' => '/', 'title' => $item['title']];
        }

        $tabList = [
            ['id' => 0, 'title' => '全部'],
        ];
        $channelList = Channel::where('status', 'normal')
            ->where('type', 'in', ['list'])
            ->field('id,parent_id,name,diyname')
            ->order('weigh desc,id desc')
            ->cache(false)
            ->select();
        foreach ($channelList as $index => $item) {
            $tabList[] = ['id' => $item['id'], 'title' => $item['name']];
        }
        $archivesList = Archives::getArchivesList([]);
        $archivesList = collection($archivesList)->toArray();
        foreach ($archivesList as $index => &$item) {
            $item['url'] = $item['fullurl'];
            unset($item['imglink'], $item['textlink'], $item['channellink'], $item['tagslist'], $item['weigh'], $item['status'], $item['deletetime'], $item['memo'], $item['img']);
        }
        $data = [
            'bannerList' => $bannerList,
            'tabList' => $tabList,
            'archivesList' => $archivesList,
        ];
        $this->success('', $data);
    }



}
