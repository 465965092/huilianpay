<?php
class Http{
	private $resCode;
	private $errInfo;
	private $resContent;
	private $header=array();
	private $buildData=array();

	function __construct($url,$data,$timeout=10){
		$this->url=$url;
		$this->data=$data;
		$this->timeout=$timeout;
	}

	 public function toUrl(){
		//启动一个CURL会话
		$ch = curl_init();

		// 设置curl允许执行的最长秒数
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		// 获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

		//发送一个常规的POST请求。
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$this->getHeader());
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
		//要传送的所有数据
		curl_setopt($ch, CURLOPT_POSTFIELDS,$this->getBuild());
		// 执行操作
		$res = curl_exec($ch);
		$this->resCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($res == NULL) {
		   $this->errInfo = "call http err :" . curl_errno($ch) . " - " . curl_error($ch) ;
		   curl_close($ch);
		   return false;
	   } else if($this->resCode != "200") {
			$this->errInfo = "call http err httpcode=" . $this->resCode  ;
			curl_close($ch);
			return false;
		}

		curl_close($ch);
		$this->resContent = $res;
		return true;
	}

	public function getResContent(){
		return $this->resContent;
	}

	public function setHeader($head='form'){
		switch($head){
			case 'form':
				$h='Content-Type:application/x-www-form-urlencoded'; break;
			case 'json':
				$h='Content-Type:application/json'; break;
			case 'xml':
				$h='Content-Type:application/xml'; break;
			case 'text':
				$h='Content-Type:text/plain'; break;
			case 'html':
				$h='Content-Type:text/html'; break;
			default:
				$h='Content-Type:application/x-www-form-urlencoded';
		}
		$this->header=array($h.';charset=utf-8');
		return $this->header;
	}

	private function getHeader(){
		return $this->header ? $this->header : $this->setHeader();
	}

	public function setBuild($build=true){
		$this->buildData=$build ? http_build_query($this->data) : $this->data;
		return $this->buildData;
	}

	private function getBuild(){
		return $this->buildData ? $this->buildData : $this->setBuild();
	}

	public function getResCode(){
		return $this->resCode;
	}

	public function getErrInfo(){
		return $this->errInfo;
	}
}
?>
