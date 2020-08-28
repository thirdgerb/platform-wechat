<?php


/**
 * Class OffcialAccountServiceProvider
 * @package Commune\Platform\Wechat\OfficialAccount
 */

namespace Commune\Platform\Wechat\OfficialAccount;

use GuzzleHttp\Client;
use Hyperf\Guzzle\CoroutineHandler;
use GuzzleHttp\HandlerStack;
use Commune\Contracts\Cache;
use Commune\Chatbot\Hyperf\Support\HttpBabel;
use Commune\Platform\Wechat\Options\EasyWechatConfig;
use EasyWeChat\OfficialAccount\Application as Wechat;
use Commune\Container\ContainerContract;
use Commune\Contracts\ServiceProvider;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request as SwooleRequest;

class OfficialAccountProvider extends ServiceProvider
{
    public function getDefaultScope(): string
    {
        return self::SCOPE_REQ;
    }

    public static function stub(): array
    {
        return [];
    }

    public function boot(ContainerContract $app): void
    {
    }

    public function register(ContainerContract $app): void
    {
        $app->singleton(Wechat::class, function(ContainerContract $app) {

            /**
             * @var EasyWechatConfig $config
             * @var Cache $cache
             */
            $config = $app->make(EasyWechatConfig::class);
            $wechat = new Wechat($config->toConfigArr());

            // rebind

            // request
            $request = $app->make(SwooleRequest::class);
            $symfonyRequest = HttpBabel::requestFromSwooleToSymfony($request);
            $wechat->rebind('request', $symfonyRequest);

            // logger
            $logger = $app->make(LoggerInterface::class);
            $wechat->rebind('log', $logger);
            $wechat->rebind('logger', $logger);

            // redis
            $cache = $app->make(Cache::class);
            $wechat->rebind('cache', $cache->getPSR16Cache());

            // client
            $clientConfig = $wechat['config']->get('http', []);
            $clientConfig['handler'] = HandlerStack::create(new CoroutineHandler());
            $client = new Client($clientConfig);
            $wechat->rebind('http_client', $client);

            return $wechat;

        });
    }


}