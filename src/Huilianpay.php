<?php

namespace Denghr\Huilianpay;

use Illuminate\Config\Repository;
use Illuminate\Session\SessionManager;

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
    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->wyapi = new \wyapi($this->config['huilianpay']);
    }

    /**
     * @param string $msg
     * @return string
     */
    public function submitOrder($params, $isapp = false)
    {
        if ($isapp) {
            return $this->wyapi->submitAppOrder($params);
        } else {
            return $this->wyapi->submitOrder($params);
        }

    }

    public function getOpenid()
    {
        $this->wyapi->getOpenid($params);
    }
}