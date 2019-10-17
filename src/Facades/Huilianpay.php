<?php
namespace Denghr\Huilianpay\Facades;
use Illuminate\Support\Facades\Facade;
class Huilianpay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'huilianpay';
    }
}