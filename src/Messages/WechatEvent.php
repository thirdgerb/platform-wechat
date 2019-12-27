<?php

/**
 * Class WechatEvent
 * @package Commune\Chatbot\Wechat\Messages
 */

namespace Commune\Platform\Wechat\Messages;

use Commune\Chatbot\Framework\Messages\AbsEvent;

class WechatEvent extends AbsEvent
{
    protected $eventName;

    public function __construct(string $eventName)
    {
        $this->eventName = $eventName;
        parent::__construct();
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

}