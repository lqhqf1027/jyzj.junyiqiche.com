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

class Job1 extends Controller
{
    public function fire(Job $job, $data)
    {
        print("<info>Hello Job has been done and deleted" . "</info>\n");
        //....这里执行具体的任务
        $data = json_decode($data, true);
        if ($this->jobDone($data)) {
            $job->delete();
            print("<info>Hello Job has been done and deleted" . "</info>\n");
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
        print("<info>Job is Done status!" . "</info> \n");
//        return 1;

//        return Db::name('tp5_test')->where(['order_no' => $data['order_no']])->update(['status' => 1]);
    }
}