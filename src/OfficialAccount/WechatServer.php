<?php

namespace Commune\Platform\Wechat\OfficialAccount;

use Commune\Blueprint\Framework\ReqContainer;
use Commune\Blueprint\Host;
use Commune\Blueprint\Platform;
use Commune\Blueprint\Shell;
use Commune\Support\Uuid\HasIdGenerator;
use Commune\Support\Uuid\IdGeneratorHelper;
use Hyperf\Contract\OnRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;


class WechatServer implements OnRequestInterface, HasIdGenerator
{
    use IdGeneratorHelper;

    /**
     * @var Host
     */
    protected $host;

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var Platform
     */
    protected $platform;

    /**
     * WechatServer constructor.
     * @param Host $host
     */
    public function __construct(Host $host)
    {
        $this->host = $host;
        $container = $host->getProcContainer();
        $this->shell = $container->make(Shell::class);
        $this->platform = $container->make(Platform::class);
    }


    public function onRequest(SwooleRequest $request, SwooleResponse $response): void
    {
        $container = $this->getContainer();
        $container->share(SwooleRequest::class, $request);

        $packer = new WechatPacker(
            $this->platform,
            $this->shell,
            $container,
            $request,
            $response,
            null
        );

        /**
         * @var OfficialAccountPlatformConfig $config
         */
        $config = $container->make(OfficialAccountPlatformConfig::class);
        $this->platform->onPacker($packer, $config->adapterName);


        $response->end();
    }


    public function getContainer() : ReqContainer
    {
        return $this->shell->newReqContainerIns($this->createUuId());
    }

}