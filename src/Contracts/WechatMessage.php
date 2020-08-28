<?php


namespace Commune\Platform\Wechat\Contracts;


use Commune\Protocals\HostMsg;
use EasyWeChat\Kernel\Contracts\MessageInterface;

interface WechatMessage extends HostMsg
{

    public function toEasyWechatMessage() : MessageInterface;

}