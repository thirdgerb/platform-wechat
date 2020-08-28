<?php


/**
 * Class WechatAudioMsg
 * @package Commune\Platform\Wechat\Shell\Messages
 */

namespace Commune\Platform\Wechat\Shell\Messages;


use Commune\Protocals\HostMsg;
use Commune\Protocals\HostMsg\Convo\Media\AudioMsg;
use Commune\Support\Message\AbsMessage;

/**
 * @property-read string $resource
 */
class WechatAudioMsg extends AbsMessage implements AudioMsg
{

    public static function instance(
        string $wechatResourceId
    ) : self
    {
        return new static(['resource' => $wechatResourceId]);
    }

    public static function stub(): array
    {
        return [
            'resource' => '',
        ];
    }

    public static function relations(): array
    {
        return [];
    }

    public function getProtocalId(): string
    {
        return $this->resource;
    }

    public function getLevel(): string
    {
        return HostMsg::INFO;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getText(): string
    {
        return $this->resource;
    }

    public function isEmpty(): bool
    {
        return false;
    }


}