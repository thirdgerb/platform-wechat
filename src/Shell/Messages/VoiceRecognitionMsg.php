<?php


/**
 * Class VoiceRecognitionMsg
 * @package Commune\Platform\Wechat\Shell\Messages
 */

namespace Commune\Platform\Wechat\Shell\Messages;


use Commune\Protocals\HostMsg;
use Commune\Protocals\HostMsg\Convo\Media\AudioMsg;
use Commune\Protocals\HostMsg\Convo\VerbalMsg;
use Commune\Support\Message\AbsMessage;

/**
 *
 * @property-read string $resource
 * @property-read string $text
 */
class VoiceRecognitionMsg extends AbsMessage implements AudioMsg, VerbalMsg
{

    public static function instance(
        string $wechatResourceId,
        string $text
    ) : self
    {
        return new static([
            'resource' => $wechatResourceId,
            'text' => $text,
        ]);
    }


    public static function stub(): array
    {
        return [
            'resource' => '',
            'text' => '',
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
        return $this->text;
    }

    public function isEmpty(): bool
    {
        return false;
    }


}