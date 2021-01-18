<?php
namespace App\Http\Controllers;

use App\Models\PortForward;
use Illuminate\Http\Request;
 
class PortForwardController extends Controller
{
    public function __construct()
    {
        $this->HandlerServiceClient = [
            '54.178.78.36' => new \Bluehead\V2ray\Core\App\Proxyman\Command\HandlerServiceClient('127.0.0.1:43200', ['credentials' => \Grpc\ChannelCredentials::createInsecure()]),
            '13.124.203.36' => new \Bluehead\V2ray\Core\App\Proxyman\Command\HandlerServiceClient('127.0.0.1:43201', ['credentials' => \Grpc\ChannelCredentials::createInsecure()]),
            '102.223.72.241' => new \Bluehead\V2ray\Core\App\Proxyman\Command\HandlerServiceClient('127.0.0.1:43202', ['credentials' => \Grpc\ChannelCredentials::createInsecure()]),
        ];
    }

    public function show(){
        $records = PortForward::where(['enable' => true])->orderBy('id','desc')->paginate(30);
        return view('show', compact('records'));
    }

    public function create(Request $request)
    {

        $recaptcha = http_build_query(
            array(
                'secret' => '6LfL5d4UAAAAAGYCwFJzM08XPJtYPfbvQwGLleYV',
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['HTTP_CF_CONNECTING_IP']
            )
        );
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $recaptcha
            )
        );
        $context  = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);
        if (!$result->success) {
            return back()->withInput()->withErrors(array('message' => '你是机器人.'));
        }

        $this->validate($request, [
            'server' => 'required',
            'dport' => 'required|integer|min:10000|max:60000',
            'client' => 'required|ipv4',
            'sport' => 'required|integer|min:1|max:65535',
        ],
            ['server.required' => '服务器信息不能为空!',
                'dport.required' => '目标端口号不能为空!',
                'client.required' => '源服务器不能为空!',
                'sport.required' => '源端口号不能为空!',
                'dport.min' => '目标端口范围只能从10000到60000之间选,并且不能选择已占用的端口,你当前选择过小!',
                'dport.max' => '目标端口范围只能从10000到60000之间选,并且不能选择已占用的端口,你当前选择过大!',
                'dport.integer' => '端口号只能是数字!',
                'sport.min' => '源端口范围只能从1到65535之间选,你当前选择过小!',
                'sport.max' => '源端口范围只能从1到65535之间选,你当前选择过大!',
                'sport.integer' => '端口号只能是数字!',
                'client.ipv4' => '源服务器只能是IPv4主机!',
            ]
        );

        if(!array_key_exists($request->get('server'),$this->HandlerServiceClient)){
            return back()->withInput()->withErrors(array('message' => '找不到对应的服务器.'));
        }

        $count = PortForward::where([
            ['server', '=', $request->get('server')],
            ['dport', '=', intval($request->get('dport'))],
            ['enable', true],
        ])->count();
        if ($count != 0) {

            $resp = PortForward::where([
                ['server', '=', $request->get('server')],
                ['dport', '=', intval($request->get('dport'))],
                ['enable', true],
            ])->get();
            $tag = $resp[0]->id;

            return back()->withInput()->withErrors(array('message' => '端口已被占用,请重新配置.(分配号:'.$tag.')'));
        }

        $r = new PortForward([
            'server' => $request->get('server'),
            'dport' => intval($request->get('dport')),
            'client' => $request->get('client'),
            'sport' => intval($request->get('sport')),
            'bw' => 0,
            'enable' => true,
        ]);
        $r->save();
        $tag = $r->id;

        $network = (new \Bluehead\V2ray\Core\Common\Net\NetworkList())->setNetwork([2,3]);
        $domain = (new \Bluehead\V2ray\Core\Common\Net\IPOrDomain())->setDomain($request->get('client'));
        $config = (new \Bluehead\V2ray\Core\Proxy\Dokodemo\Config())->setAddress($domain)->setPort(intval($request->get('sport')))->setNetworkList($network);
        $config = (new \Bluehead\V2ray\Core\Common\Serial\TypedMessage())->setValue($config->serializeToString())->setType('v2ray.core.proxy.dokodemo.Config');
        $localhost = (new \Bluehead\V2ray\Core\Common\Net\IPOrDomain())->setIp('0.0.0.0');
        $dport = (new \Bluehead\V2ray\Core\Common\Net\PortRange)->setFrom($request->get('dport'))->setTo($request->get('dport'));
        $receiver = (new \Bluehead\V2ray\Core\App\Proxyman\ReceiverConfig())->setPortRange($dport)->setListen($localhost);
        $receiver = (new \Bluehead\V2ray\Core\Common\Serial\TypedMessage())->setValue($receiver->serializeToString())->setType('v2ray.core.app.proxyman.ReceiverConfig');
        $inbound = (new \Bluehead\V2ray\Core\InboundHandlerConfig())->setTag($tag)->setProxySettings($config)->setReceiverSettings($receiver);
        $inbound = (new \Bluehead\V2ray\Core\App\Proxyman\Command\AddInboundRequest())->setInbound($inbound);
        $call = $this->HandlerServiceClient[$request->get('server')]->AddInbound($inbound);
        
        list($resp, $status) = $call->wait();
        if($status->code != 0){
            $resp = PortForward::where('_id', '=', $tag)->update(['enable' => false]);
            return back()->withInput()->withErrors(array('message' => '后端服务器出错,分配已取消!'));
        }else{
            session()->flash('success', '创建成功啦,现在连接'.$request->get('server').':'.$request->get('dport').'试试吧!');
            return redirect()->route('index');
        }
    }
}
