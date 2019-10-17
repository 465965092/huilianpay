<?php
require_once 'http.class.php';
require_once 'xml.class.php';
require_once 'encrypt.class.php';
require_once 'libs/XmlseclibsAdapter.php';

class wyapi{
    public $debug=true;
    public $userkey;
    public $privateKey = '';
    public $publicKey;
    public $IsvOrgId = "";
    public $gateUrl='https://open.huilianpay.com/pay';

    function __construct($privateKey='',$publicKey=''){
        $this->xml=new Xml();
        $this->head=array(
            'Version'=>'1.0.0',
            'Appid'=>'',
            'ReqTime'=>date('Y-m-d H:i:s.275'),//2019-04-23 20:32:53.275
            'ReqTimeZone'=>'UTC+8',
            'ReqMsgId'=>$this->getRandomString(),
            //'ReqMsgId'=>"9e9c2321-6304-4e3d-a6bf-aa".rand(1000,9999)."2869af",
            'RsaType' => "01",
//            'SignType'=>'RSA',
            'ProviderType'=>'01',
            'InputCharset'=>'UTF-8',
        );
        $this->publicKey = $publicKey;
		var_dump(11111);exit;
//        $this->privateKey = $privateKey;
    }


    public function getOpenid(){

        $redirectUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);//$_SERVER['QUERY_STRING']

        $attach = base64_encode($_SERVER['QUERY_STRING']);
        $url = "https://open.huilianpay.com/output/openId?redirectUrl={$redirectUrl}&attach={$attach}";
        header("location:" . $url);
    }

    public function getRandomString(){
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8)
            .substr($charid, 8, 4)
            .substr($charid,12, 4)
            .substr($charid,16, 4)
            .substr($charid,20,12);
        return $uuid;
    }

    public function submitOrder($data){
        $this->head['Function']='ant.mybank.bkmerchanttrade.prePay';
        $data+=array(
            'Currency'=>'CNY',
            'DeviceCreateIp'=>"127.0.0.1",
            'ChannelType'=>'WX',
            'SettleType'=>'T1',
            //'RsaType' => "02",
            'ProviderType'=>'01',
            'IsvOrgId'=>$this->IsvOrgId,
        );

        $resultXml=$this->submit($data);
        $this->log('submitOrderResult',$resultXml);

        $objectxml = (array)simplexml_load_string($resultXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$jsonStr = json_encode($objectxml);
        $ret2 = json_decode($jsonStr,true);

        $ret=$ret2['response']['body']['RespInfo'];
        if($ret['ResultStatus']=='true' || $ret['ResultStatus']=='S'){
            if($this->verify($resultXml)){
                $payInfo=$ret2['response']['body']['PayInfo'];
                return array('status'=>1,'url'=>$payInfo);
            }
        }
        return array('status'=>0,'msg'=>$ret['ResultCode'].' | '.$ret['ResultMsg']);
    }


    public function submitOrder2($data){
        $this->head['Function']='ant.mybank.bkmerchanttrade.prePay';
        $data+=array(
            'Currency'=>'CNY',
            'DeviceCreateIp'=>"127.0.0.1",
            'ChannelType'=>'WX',
            'SettleType'=>'T1',
            //'RsaType' => "02",
            'ProviderType'=>'01',
            'IsvOrgId'=>$this->IsvOrgId,
        );

        $resultXml=$this->submit($data);
        $this->log('submitOrderResult',$resultXml);

        $objectxml = (array)simplexml_load_string($resultXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $jsonStr = json_encode($objectxml);
        $ret2 = json_decode($jsonStr,true);

        $ret=$ret2['response']['body']['RespInfo'];
        if($ret['ResultStatus']=='true' || $ret['ResultStatus']=='S'){
            if($this->verify($resultXml)){
                $payInfo=$ret2['response']['body'];
                return array('status'=>1,'data'=>$payInfo);
            }
        }
        return array('status'=>0,'msg'=>$ret['ResultCode'].' | '.$ret['ResultMsg']);
    }

    public function submitRefund($data){
        $this->head['Function']='ant.mybank.bkmerchanttrade.refund';
        $data+=array(
            'IsvOrgId'=>$this->IsvOrgId,
        );
        $resultXml=$this->submit($data);
        $this->log('submitRefund',$resultXml);

        $objectxml = (array)simplexml_load_string($resultXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $jsonStr = json_encode($objectxml);
        $ret2 = json_decode($jsonStr,true);

        $ret=$ret2['response']['body']['RespInfo'];
        if($ret['ResultStatus']=='true' || $ret['ResultStatus']=='S'){
            if($this->verify($resultXml)){
                return array('status'=>1,'data'=>$ret2['response']['body']);
            }
        }
        return array('status'=>0,'msg'=>$ret['ResultCode'].' | '.$ret['ResultMsg']);
    }

    public function notifyOrder(){
        $resultXml=file_get_contents('php://input');
        $this->log('notifyOrder',$resultXml);

        $objectxml = (array)simplexml_load_string($resultXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$jsonStr = json_encode($objectxml);
        $ret2 = json_decode($jsonStr,true);

        $ret=$ret2['request']['body'];
        if($this->verify($resultXml)){
            $this->log('submitOrderResult01','ok');
            
            return $ret;
        }
        return false;
    }

    public function queryOrder($params){
        $this->head['Function']='ant.mybank.bkmerchanttrade.payQuery';
        $data=array(
            'HlMerchantId'=>$params['HlMerchantId'],
            'IsvOrgId'=>$this->IsvOrgId,
            'OutTradeNo'=>$params['out_trade_no'],
        );

        $resultXml=$this->submit($data);
        $this->log('orderQueryResult',$resultXml);

        $objectxml = (array)simplexml_load_string($resultXml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$jsonStr = json_encode($objectxml);
        $ret = json_decode($jsonStr,true);

       
        
        $ret=$ret['response']['body'];
        if($ret['TradeStatus']=='succ'){
            if($this->verify($resultXml)){
                return array(
                    'orderid'=>$ret['OrderNo'],
                    'total_fee'=>$ret['TotalAmount'],
                );
            }
        }
        return false;
    }

    public function submit($data){
        $xml='<document><request><head>'.$this->xml->toXml($this->head).'</head><body>'.$this->xml->toXml($data).'</body></request></document>';

        $pass_key = $this->privateKey;
    	$pass_key = chunk_split($pass_key, 64, "\n");
    	$private_key = "-----BEGIN RSA PRIVATE KEY-----\n$pass_key-----END RSA PRIVATE KEY-----\n";
    	$xmlTool = new XmlseclibsAdapter();
    	$xmlTool->setPrivateKey($private_key);
		$xmlTool->addTransform(XmlseclibsAdapter::ENVELOPED);
		$xmlDocument = new DOMDocument();
        $xmlDocument->loadXML(trim($xml));
		$xmlTool->sign($xmlDocument);
        $post_data = $xmlDocument->saveXML();
        $this->log('request',$post_data);
        $http=new Http($this->gateUrl,$post_data);
        $http->setBuild(0);
        $http->setHeader('xml');
        $http->toUrl();
        $resultXml=$http->getResContent();

        return $resultXml;
    }

    public function verify($xml){
        $pass_key = $this->publicKey;
    	$pass_key = chunk_split($pass_key, 64, "\n");
        $public_key = "-----BEGIN PUBLIC KEY-----\n$pass_key-----END PUBLIC KEY-----\n";
    	$xmlTool = new XmlseclibsAdapter();
        $xmlTool->setPublicKey($public_key);
        $xmlTool->addTransform(XmlseclibsAdapter::ENVELOPED);
        $xmlTool->setKeyAlgorithm(XMLSecurityKey::RSA_SHA256);
		$xmlDocument = new DOMDocument();
        $xmlDocument->loadXML(trim($xml));
        return $xmlTool->verify($xmlDocument);
    }

    public function log($title,$data){
        if(!$this->debug) return false;
        $content = date("Y-m-d H:i:s")."================".$title."===================\n";
        if(is_string($data) === true){
            $content .= $data."\n";
        }
        if(is_array($data) === true){
            $i=0;
            foreach($data as $k=>$v){
                $i+=1;
                $i2=$i<10 ? '0'.$i : $i;
                $content .= $i2.": ".$k." = ".$v."\n";
            }
        }

        file_put_contents(dirname(__FILE__)."/lllog/".date("Ymd").".log",$content,FILE_APPEND);

    }
}
?>
