<?php
/**
 * Created by PhpStorm.
 * User: glen9
 * Date: 2019/5/17
 * Time: 16:37
 */

namespace app\job;

use think\queue\Job;
use think\Db;
use think\Controller;

class CommonJob extends Controller
{
    /**
     * 获取违章信息(单个、批量)
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function sendMessagePerson(Job $job, $data)
    {

        //....这里执行具体的任务

        if ($this->jobDone($data)) {
            $job->delete();
//            print("<info>var_export($data,true)" . "</info>\n");
        } else {
            $job->release(3); //$delay为延迟时间
        }
        if ($job->attempts() > 3) {
            //通过这个方法可以检查这个任务已经重试了几次了
        }
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        // $job->delete();
        // 也可以重新发布这个任务
        // $job->release($delay); //$delay为延迟时间
    }

    public function failed($data)
    {
        print('任务达到最大重试次数后，失败了');
        // ...任务达到最大重试次数后，失败了
    }

    public function jobDone($data)
    {
        illegal($data);
        print("<info> ".var_export($data,true)."</info> \n");
//        illegal(json_decode($data, true));
//        print("<info>Job is Done status!" . "</info> \n");
        return true;

//        return Db::name('tp5_test')->where(['order_no' => $data['order_no']])->update(['status' => 1]);
    }
}