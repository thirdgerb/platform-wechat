<?php


/**
 * Class WechatPlatformConfig
 * @package Commune\Platform\Wechat
 */

namespace Commune\Platform\Wechat;


use Commune\Platform\Wechat\OfficialAccount\WechatAdapter;
use Commune\Platform\Wechat\OfficialAccount\WechatServer;
use Commune\Platform\Wechat\Options\EasyWechatConfig;
use Commune\Platform\IPlatformConfig;
use Commune\Platform\Wechat\OfficialAccount\OfficialAccountPlatformConfig;
use Commune\Platform\Wechat\OfficialAccount\OfficialAccountProvider;
use Commune\Platform\Wechat\OfficialAccount\WechatPlatform;

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
                // 日志配置.
                LoggerByMonologProvider::class => [
                    'name' => 'chatlog',
                    'forceRegister' => true,
                ],
            ],
            'options' => [
                OfficialAccountPlatformConfig::class => [
                    'host' => env('WECHAT_PLATFORM_HOST', '127.0.0.1'),
                    'port' => env('WECHAT_PLATFORM_PORT', 10805),
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