<?php
namespace Denghr\Huilianpay;
use Illuminate\Session\SessionManager;
use Illuminate\Config\Repository;
require_once "lib\hl_wxgzh\wyapi.class.php";
class Huilianpay
{
    /**
     * @var SessionManager
     */
    protected $session;
    /**
     * @var Repository
     */
    protected $config;

    protected $wyapi;
    /**
     * Packagetest constructor.
     * @param SessionManager $session
     * @param Repository $config
     */
    public function __construct(SessionManager $session, Repository $config)
    {
        $this->session = $session;
        $this->config = $config;
        $this->wyapi = new \wyapi();
    }
    /**
     * @param string $msg
     * @return string
     */
    public function test_rtn($msg = ''){
       return "test";
    }
}