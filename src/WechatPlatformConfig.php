<?php


/**
 * Class WechatPlatformConfig
 * @package Commune\Platform\Wechat
 */

namespace Commune\Platform\Wechat;


use Commune\Chatbot\Hyperf\Servers\HfPlatformOption;
use Commune\Platform\Wechat\OfficialAccount\WechatAdapter;
use Commune\Platform\Wechat\OfficialAccount\WechatServer;
use Commune\Platform\Wechat\Options\EasyWechatConfig;
use Hyperf\Server\Server;
use Commune\Chatbot\Hyperf\Servers\HfServerOption;
use Commune\Platform\IPlatformConfig;
use Commune\Platform\Wechat\OfficialAccount\OfficialAccountPlatformConfig;
use Commune\Platform\Wechat\OfficialAccount\OfficialAccountProvider;
use Commune\Platform\Wechat\OfficialAccount\WechatPlatform;
use Hyperf\Server\SwooleEvent;

class WechatPlatformConfig extends IPlatformConfig
{

    public static function stub(): array
    {
        return [
            'id' => '',
            'name' => '',
            'desc' => '',
            'bootShell' => null,
            'concrete' => WechatPlatform::class,
            'bootGhost' => false,
            'providers' => [
                OfficialAccountProvider::class,
            ],
            'options' => [
                OfficialAccountPlatformConfig::class => [
                    'host' => '127.0.0.1',
                    'port' => 9503,
                    'httpServer' => WechatServer::class,


                    'processes' => [],
                    'callbacks' => [],
                    'settings' => [],
                    'exceptionHandlers' => [],


                    'adapterName' => WechatAdapter::class,
                    'entries' => [],
                    'unsupportedReply' => '回复消息类型在微信上不支持.',
                ],
                EasyWechatConfig::class => [
                    'app_id'  => env('WECHAT_APP_ID', 'your-app-id'),
                    // AppID
                    'secret'  => env('WECHAT_SECRET', 'your-app-secret'),
                    // AppSecret
                    'token'   => env('WECHAT_TOKEN', 'your-token'),
                    // Token
                    'aes_key' => env('WECHAT_AES_KEY', ''),
                ],
            ],
        ];
    }

}