<?php


/**
 * Class WechatPlatform
 * @package Commune\Platform\Wechat\OfficialAccount
 */

namespace Commune\Platform\Wechat\OfficialAccount;


use Commune\Chatbot\Hyperf\Servers\AbsHyperfServerPlatform;
use Commune\Chatbot\Hyperf\Servers\HfPlatformOption;

class WechatPlatform extends AbsHyperfServerPlatform
{
    protected function initializeHyperf(): void
    {
    }

    public function getHyperfPlatformOption(): HfPlatformOption
    {
        /**
         * @var OfficialAccountPlatformConfig $config
         */
        $config = $this->host
            ->getProcContainer()
            ->make(OfficialAccountPlatformConfig::class);

        return $config->toHfPlatformConfig();
    }


}