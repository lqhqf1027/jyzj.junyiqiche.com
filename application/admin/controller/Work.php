<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/5/21
 * Time: 10:07
 */

namespace app\admin\controller;

use think\queue\Job;
use think\Db;

class Work
{
    public function fire(Job $job, $data)
    {
        $isJobDone = $this->send($data);
        if ($isJobDone) {
            //成功删除任务
            $job->delete();
        } else {
            //任务轮询4次后删除
            if ($job->attempts() > 3) {
                // 第1种处理方式：重新发布任务,该任务延迟10秒后再执行
                //$job->release(10);
                // 第2种处理方式：原任务的基础上1分钟执行一次并增加尝试次数
                //$job->failed();
                // 第3种处理方式：删除任务
                $job->delete();
            }
        }
    }

    /**
     * 根据消息中的数据进行实际的业务处理
     * @param array|mixed $data 发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function send($data)
    {
        $result = Db::name('tp5_test')->insert([
            'create_time' => time()
        ]);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function queue()
    {
        // \think\Queue::push('app\admin\controller\Queue@fire', '284560@qq.com', $queue = '777');
        // \think\Queue::push('app\common\queue\QueueClient@sendMAIL', '28456049@qq.com', $queue = null);
        \think\Queue::push('app\admin\controller\Work@fire', '28456049@qq.com', $queue = '好队列');
        echo 'ok';
    }

}