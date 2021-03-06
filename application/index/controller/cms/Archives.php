<?php

namespace app\index\controller\cms;

use addons\cms\model\Channel;
use addons\cms\model\Modelx;
use app\common\controller\Frontend;
use fast\Tree;
use think\Exception;
use think\Validate;

/**
 * 会员中心
 */
class Archives extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 发表文章
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function post()
    {
        $id = $this->request->get('id');
        $archives = $id ? \app\admin\model\cms\Archives::get($id) : null;

        if ($archives) {
            $channel = Channel::get($archives['channel_id']);
            if (!$channel) {
                $this->error(__('未找到指定栏目'));
            }
            $model = \addons\cms\model\Modelx::get($channel['model_id']);
            if (!$model) {
                $this->error(__('未找到指定模型'));
            }
            if ($archives['user_id'] != $this->auth->id) {
                $this->error("无法进行越权操作！");
            }
        } else {
            $model = null;
            $model_id = $this->request->request('model_id');
            // 如果有model_id则调用指定模型
            if ($model_id) {
                $model = Modelx::get($model_id);
            }
        }

        // 如果来源于提交
        if ($this->request->isPost()) {
            $row = $this->request->post('row/a');
            $token = $this->request->post('token');
            $rule = [
                'title|标题'      => 'require|length:3,100',
                'channel_id|栏目' => 'require|integer',
                'content|内容'    => 'require',
                '__token__'     => 'token',
            ];

            $msg = [
                'title.require'   => '标题不能为空',
                'title.length'    => '标题长度限制在3~100个字符',
                'channel_id'      => '栏目不能为空',
                'content.require' => '内容不能为空',
            ];
            $row['__token__'] = $token;
            $validate = new Validate($rule, $msg);
            $result = $validate->check($row);
            if (!$result) {
                $this->error($validate->getError(), null, ['token' => $this->request->token()]);
            }

            $row['user_id'] = $this->auth->id;
            $row['status'] = 'hidden';
            try {
                if ($archives) {
                    $archives->allowField(true)->save($row);
                } else {
                    (new \app\admin\model\cms\Archives)->allowField(true)->save($row);
                }
            } catch (Exception $e) {
                $this->error("发生错误:" . $e->getMessage());
            }
            $this->success("发布成功！请等待审核!");
            exit;
        }

        // 合并主副表
        if ($archives) {
            $addon = db($model['table'])->where('id', $archives['id'])->find();
            if ($addon) {
                $archives = array_merge($archives->toArray(), $addon);
            }
        }

        $channel = new Channel();
        if ($model) {
            $channel->where('model_id', $model['id']);
        }
        // 读取可发布的栏目列表
        $disabledIds = [];
        $channelList = collection(
            $channel->where('type', '<>', 'link')
                ->where("((type='list' AND iscontribute='1') OR type='channel')")
                ->order("weigh desc,id desc")
                ->cache(false)
                ->select()
        )->toArray();
        $channelParents = [];
        foreach ($channelList as $index => $item) {
            if ($item['parent_id']) {
                $channelParents[] = $item['parent_id'];
            }
        }
        foreach ($channelList as $k => $v) {
            if ($v['type'] != 'list') {
                $disabledIds[] = $v['id'];
            }
            if ($v['type'] == 'channel' && !in_array($v['id'], $channelParents)) {
                unset($channelList[$k]);
            }
        }
        $tree = Tree::instance()->init($channelList, 'parent_id');
        $channelOptions = $tree->getTree(0, "<option model='@model_id' value=@id @selected @disabled>@spacer@name</option>", $archives ? $archives['channel_id'] : '', $disabledIds);
        $this->view->assign('channelOptions', $channelOptions);
        $this->view->assign([
            'archives'       => $archives,
            'channelOptions' => $channelOptions,
            'categoryList'   => ''
        ]);
        $this->assignconfig('archives_id', $archives ? $archives['id'] : 0);

        $modelName = $model ? $model['name'] : '文章';
        $this->view->assign('title', $archives ? "修改{$modelName}" : "发布{$modelName}");
        $this->view->assign('model', $model);
        return $this->view->fetch();
    }

    /**
     * 我的发布
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function my()
    {
        $archives = new \addons\cms\model\Archives;
        $model = null;
        $model_id = $this->request->request('model_id');
        // 如果有model_id则调用指定模型
        if ($model_id) {
            $model = Modelx::get($model_id);
            if ($model) {
                $archives->where('model_id', $model_id);
            }
        }
        $config = ['query' => []];
        if ($model) {
            $config['query']['model_id'] = $model_id;
        }
        $archivesList = $archives->where('user_id', $this->auth->id)
            ->order('id', 'desc')
            ->paginate(10, null, $config);
        $this->view->assign('archivesList', $archivesList);
        $this->view->assign('title', '我发布的' . ($model ? $model['name'] : '文章'));
        $this->view->assign('model', $model);
        return $this->view->fetch();
    }

    /**
     * 获取栏目列表
     * @internal
     */
    public function get_channel_fields()
    {
        $this->view->engine->layout(false);
        $channel_id = $this->request->post('channel_id');
        $archives_id = $this->request->post('archives_id');
        $channel = Channel::get($channel_id, 'model');
        if ($channel && $channel['type'] === 'list') {
            $values = [];
            if ($archives_id) {
                $values = db($channel['model']['table'])->where('id', $archives_id)->find();
            }

            $fields = \app\admin\model\cms\Fields::where('model_id', $channel['model_id'])
                ->where('iscontribute', 1)
                ->where('status', 'normal')
                ->order('weigh desc,id desc')
                ->select();
            foreach ($fields as $k => $v) {
                //优先取编辑的值,再次取默认值
                $v->value = isset($values[$v['name']]) ? $values[$v['name']] : (is_null($v['defaultvalue']) ? '' : $v['defaultvalue']);
                $v->rule = str_replace(',', '; ', $v->rule);
                if (in_array($v->type, ['checkbox', 'lists', 'images'])) {
                    $checked = '';
                    if ($v['minimum'] && $v['maximum']) {
                        $checked = "{$v['minimum']}~{$v['maximum']}";
                    } elseif ($v['minimum']) {
                        $checked = "{$v['minimum']}~";
                    } elseif ($v['maximum']) {
                        $checked = "~{$v['maximum']}";
                    }
                    if ($checked) {
                        $v->rule .= (';checked(' . $checked . ')');
                    }
                }
                if (in_array($v->type, ['checkbox', 'radio']) && stripos($v->rule, 'required') !== false) {
                    $v->rule = str_replace('required', 'checked', $v->rule);
                }
                if (in_array($v->type, ['selects'])) {
                    $v->extend .= (' ' . 'data-max-options="' . $v['maximum'] . '"');
                }
            }

            $this->view->assign('fields', $fields);
            $this->view->assign('values', $values);
            $this->success('', null, ['html' => $this->view->fetch('fields')]);
        } else {
            $this->error(__('请选择栏目'));
        }
        $this->error(__('参数不能为空', 'ids'));
    }

    public function tags_autocomplete()
    {
        $q = $this->request->request('q');
        $list = \addons\cms\model\Tags::where('name', 'like', '%' . $q . '%')->column('name');
        echo json_encode($list);
        return;
    }
}
