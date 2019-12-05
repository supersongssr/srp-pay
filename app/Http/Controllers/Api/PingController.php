<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;
//song
use App\Http\Models\SsNodeTrafficHourly;
use App\Http\Models\UserTrafficLog;
use App\Http\Models\SsNode;
use Illuminate\Console\Command;
use App\Http\Models\SsNodeOnlineLog;



/**
 * PING检测工具
 *
 * Class PingController
 *
 * @package App\Http\Controllers\Api
 */
class PingController extends Controller
{
    public function ping(Request $request)
    {
        $token = $request->input('token');
        $host = $request->input('host');
        $port = $request->input('port', 22);
        $transport = $request->input('transport', 'tcp');
        $timeout = $request->input('timeout', 0.5);

        if (empty($host)) {
            echo "<pre>";
            echo "使用方法：";
            echo "<br>";
            echo "GET /api/ping?token=toke_value&host=www.baidu.com&port=80&transport=tcp&timeout=0.5";
            echo "<br>";
            echo "token：.env下加入API_TOKEN，其值就是token的值";
            echo "<br>";
            echo "host：检测地址，必传，可以是域名、IPv4、IPv6";
            echo "<br>";
            echo "port：检测端口，可不传，默认22";
            echo "<br>";
            echo "transport：检测协议，可不传，默认tcp，可以是tcp、udp";
            echo "<br>";
            echo "timeout：检测超时，单位秒，可不传，默认0.5秒，建议不超过3秒";
            echo "<br>";
            echo "成功返回：1，失败返回：0";
            echo "</pre>";
            exit();
        }

        // 验证TOKEN，防止滥用
        if (env('API_TOKEN') != $token) {
            return response()->json(['status' => 0, 'message' => 'token invalid']);
        }

        // 如果不是IPv4
        if (false === filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // 如果是IPv6
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $host = '[' . $host . ']';
            }
        }

        try {
            $host = gethostbyname($host); // 这里如果挂了，说明服务器的DNS解析不给力，必须换
            $fp = stream_socket_client($transport . '://' . $host . ':' . $port, $errno, $errstr, $timeout);
            if (!$fp) {
                Log::info("$errstr ($errno)");
                $ret = 0;
                $message = 'port close';
            } else {
                $ret = 1;
                $message = 'port open';
            }

            fclose($fp);

            return response()->json(['status' => $ret, 'message' => $message]);
        } catch (\Exception $e) {
            Log::info($e);

            return response()->json(['status' => 0, 'message' => 'port close']);
        }
    }

    public function ssn_sub(Request $request, $id)
    {
        $status = $request->get('status');
        $traffic = $request->get('traffic');
        $online = $request->get('online');
        //获取NODE数据
        $node = SsNode::query()->where('id', $id)->first();
        $traffic_mark = $node['traffic'];
        //写入node数据 status
        SsNode::query()->where('id',$id)->update(['status'=>$status,'traffic'=>$traffic]);
        empty($node['monitor_url']) && exit;  //如果ssn关键数据为空，剩下的流量就不写了。 正常节点有正常写入流量的
        //写入每小时节点流量
        //直接写入用户流量数据
        $obj = new UserTrafficLog();
        $traffic_now = $traffic - $traffic_mark;
        $traffic_now < 0 && $traffic_now = 1;    //如果流量差<0 那么可能是重置了 设为1 
        $obj->user_id =  0; //用户为0的使用的流量就是上传的流量
        $obj->u = 0;
        $obj->d = $traffic_now;
        $obj->node_id = $id;
        $obj->rate = 1;
        $obj->traffic = floor($traffic_now / 1048576) . 'MB';
        $obj->log_time = time();
        $obj->save();
        /**
        $obj = new SsNodeTrafficHourly();
        $traffic_now = $traffic - $traffic_mark;
        $traffic_now < 0 && $traffic_now = 1;    //如果流量差<0 那么可能是重置了 设为1 
        $obj->node_id = $id;
        $obj->u = 0;
        $obj->d = $traffic_now;
        $obj->total = $traffic_now;
        $obj->traffic = floor($traffic_now / 1048576) . 'MB';
        $obj->save();
        **/
        //写入节点在线人数
        $online_log = new SsNodeOnlineLog();
        $online_log->node_id = $id;
        $online_log->online_user = $online;
        $online_log->log_time = time();
        $online_log->save();
    }

    public function ssn_v2(Request $request, $id)
    {
        //$id < 32 && exit;   #id 小于32的没有需求 直接退出
        $node = SsNode::query()->where('id', $id)->first();
        $s1 = $request->get('s1');
        $v2 = $request->get('v2');
        //写入节点数据
        if ($node['type'] == 1 && !empty($s1)) {
            # code...
            //empty($node['monitor_url']) && exit; #如果关键数据为空，直接退出
            //empty($node['monitor_url']) && exit;
            $addn = explode('#',$s1);
            $data = [
                'ip'=>$addn['0'] , 
                'ssh_port'=>$addn['1'], 
                'monitor_url'=>$addn['2'], 
                'method'=>$addn['3']
            ];
            SsNode::query()->where('id',$id)->update($data);
        }
        if ($node['type'] == 2 && !empty($v2)) {
            # code...
            $addn = explode('#',$v2);
            $data = [
                'ip'=>$addn['0'], 
                'v2_port'=>$addn['1'],
                'monitor_url'=>$addn['2'],
                'v2_alter_id'=>$addn['3'], 
                'v2_net'=>$addn['4'], 
                'v2_type'=>$addn['5'],
                'v2_host'=>'',
                'v2_path'=>'',
                'v2_tls'=>'0'
            ];
            if (count($addn) > 8) {
                # code...
                $data['v2_host'] = $addn['6'];
                $data['v2_path'] = '/'.$addn['7'];
                $data['v2_tls'] = empty($addn['8']) ? '0': '1';
            }

            SsNode::query()->where('id',$id)->update($data);
        }
    }
}
