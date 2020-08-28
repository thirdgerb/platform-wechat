<?php


/**
 * Class EasyWechatConfig
 * @package Commune\Platform\Wechat\Options
 */

namespace Commune\Platform\Wechat\Options;


use Commune\Support\Option\AbsOption;

/**
 * Class EasyWechatConfig
 *
 *
 * @property-read string $app_id
 * @property-read string $secret
 * @property-read string $token
 * @property-read string $aes_key
 */
class EasyWechatConfig extends AbsOption
{
    public static function stub(): array
    {
        return [
            'app_id'  => '',
            // AppID
            'secret'  => '',
            // AppSecret
            'token'   => '',
            // Token
            'aes_key' => '',

        ];
    }

    public static function relations(): array
    {
        return [];
    }


    public function toConfigArr() : array
    {
        return $this->toArray();
    }
}