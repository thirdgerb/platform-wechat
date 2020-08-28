<?php


/**
 * Class WechatPacker
 * @package Commune\Platform\Wechat\OfficialAccount
 */

namespace Commune\Platform\Wechat\OfficialAccount;


use Commune\Blueprint\Framework\ReqContainer;
use Commune\Blueprint\Kernel\Protocals\AppRequest;
use Commune\Blueprint\Platform;
use Commune\Blueprint\Platform\Adapter;
use Commune\Blueprint\Platform\Packer;
use Commune\Blueprint\Shell;
use Psr\Log\LoggerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class WechatPacker implements Packer
{
    /**
     * @var Platform
     */
    public $platform;

    /**
     * @var Shell
     */
    public $shell;

    /**
     * @var ReqContainer
     */
    public $reqContainer;

    /**
     * @var SwooleRequest|null
     */
    public $request;

    /**
     * @var SwooleResponse|null
     */
    public $response;

    /**
     * @var AppRequest|null
     */
    public $appRequest;

    /**
     * WechatPacker constructor.
     * @param Platform $platform
     * @param Shell $shell
     * @param ReqContainer $reqContainer
     * @param null|SwooleRequest $request
     * @param null|SwooleResponse $response
     * @param AppRequest|null $appRequest
     */
    public function __construct(
        Platform $platform,
        Shell $shell,
        ReqContainer $reqContainer,
        SwooleRequest $request = null,
        SwooleResponse $response = null,
        AppRequest $appRequest = null
    )
    {
        $this->platform = $platform;
        $this->shell = $shell;
        $this->reqContainer = $reqContainer;
        $this->request = $request;
        $this->response = $response;
        $this->appRequest = $appRequest;
    }


    public function isInvalidInput(): ? string
    {
        return isset($this->request)
            ? null
            : "not http request";
    }

    public function adapt(string $adapterName, string $appId): Adapter
    {
        return new $adapterName($this, $appId);
    }

    public function fail(string $error): void
    {
        /**
         * @var LoggerInterface $logger
         */
        $logger = $this->reqContainer->make(LoggerInterface::class);
        $logger->error($error);

        if (isset($this->response)) {
            $this->response->write('failure');
        }
    }

    public function destroy(): void
    {
        unset(
            $this->platform,
            $this->shell,
            $this->reqContainer,
            $this->request,
            $this->response,
            $this->appRequest
        );
    }


}