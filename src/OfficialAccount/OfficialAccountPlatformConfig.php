<?php


/**
 * Class WechatServerOption
 * @package Commune\Platform\Wechat\OfficialAccount
 */

namespace Commune\Platform\Wechat\OfficialAccount;


use Commune\Support\Option\AbsOption;
use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;
use Commune\Chatbot\Hyperf\Servers\HfPlatformOption;

/**
 *
 *
 * @property-read string $name
 * @property-read string $host                  监听地址
 * @property-read int $port                     端口
 * @property-read string $httpServer            响应 http 请求的 server
 *
 * @property-read string[] $processes           hyperf 服务器进程
 * @property-read array $settings               hyperf server settings
 * @property-read string[] $callbacks           hyperf callbacks
 * @property-read string[] $exceptionHandlers   hyperf exception handlers
 *
 * @property-read string $adapterName           适配器
 * @property-read string[] $entries             scene => entry
 * @property-read string $unsupportedReply      回复消息
 */
class OfficialAccountPlatformConfig extends AbsOption
{
    public static function stub(): array
    {
        return [
            'name' => 'wechat',
            'host' => '127.0.0.1',
            'port' => 9503,
            'httpServer' => WechatServer::class,


            'processes' => [
                WechatAsyncProcess::class,
            ],
            'callbacks' => [],
            'settings' => [],
            'exceptionHandlers' => [],



            'adapterName' => WechatAdapter::class,
            'entries' => [

            ],
            'unsupportedReply' => '回复消息类型在微信上不支持.',
        ];
    }

    public static function relations(): array
    {
        return [];
    }


    public function toHfPlatformConfig() : HfPlatformOption
    {
        return new HfPlatformOption([
            'mode' => SWOOLE_PROCESS,
            'servers' => [
                [
                    'name' => $this->name,
                    'host' => $this->host,
                    'port' => $this->port,
                    'type' => Server::SERVER_HTTP,
                    'mode' => SWOOLE_PROCESS,
                    'sock_type' => SWOOLE_SOCK_TCP,
                    'callbacks' => [
                        SwooleEvent::ON_REQUEST => [
                            $this->httpServer,
                            'onRequest'
                        ],
                    ],
                    'settings' => [
                    ],
                    'middlewares' => [
                    ],
                    'exceptionHandlers' => $this->exceptionHandlers,
                ]
            ],
            'type' => Server::class,
            'processes' => $this->processes,
            'settings' => $this->settings,
            'callbacks' => $this->callbacks,
        ]);
    }
}