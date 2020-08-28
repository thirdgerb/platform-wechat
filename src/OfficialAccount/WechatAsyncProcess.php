<?php


namespace Commune\Platform\Wechat\OfficialAccount;


use Commune\Blueprint\Host;
use Commune\Blueprint\Kernel\Handlers\ShellOutputReqHandler;
use Commune\Blueprint\Kernel\Protocals\ShellOutputRequest;
use Commune\Blueprint\Platform;
use Commune\Blueprint\Shell;
use Commune\Contracts\Log\ExceptionReporter;
use Commune\Contracts\Messenger\Broadcaster;
use Hyperf\Process\AbstractProcess;
use Hyperf\Utils\ApplicationContext;
use Psr\Log\LoggerInterface;


class WechatAsyncProcess extends AbstractProcess
{
    /**
     * @var string
     */
    public $name = 'message_broadcast';

    /**
     * @var int
     */
    public $nums = 1;

    /**
     * @var Host
     */
    protected $host;

    /**
     * @var ExceptionReporter
     */
    protected $expHandler;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Broadcaster
     */
    protected $broadcaster;

    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var WechatPlatform
     */
    protected $platform;

    public function handle(): void
    {
        $container = ApplicationContext::getContainer();
        $host = $container->get(Host::class);
        $this->init($host);

        /**
         * @var Broadcaster $broadcaster
         */
        $broadcaster = $this
            ->host
            ->getProcContainer()
            ->make(Broadcaster::class);

        $broadcaster->subscribe(
            [$this, 'receiveAsyncRequest'],
            $this->shell->getId(),
            null // shell 的全部都监听.
        );
    }

    protected function init(Host $host)
    {
        $this->host = $host;
        $procContainer = $this->host->getProcContainer();
        $this->expHandler = $procContainer->make(ExceptionReporter::class);
        $this->logger = $procContainer->make(LoggerInterface::class);
        $this->shell = $procContainer->make(Shell::class);
        $this->platform = $procContainer->make(Platform::class);
    }


    public function receiveAsyncRequest(string $chan, ShellOutputRequest $request) : void
    {
        $packer = new WechatPacker(
            $this->platform,
            $this->shell,
            $this->shell->newReqContainerIns($request->getBatchId()),
            null,
            null,
            $request
        );

        $adapterName = $this->platform->getOfficialAccountConfig()->adapterName;
        $adapter = $packer->adapt($adapterName, $this->shell->getId());
        $this->platform->onAdapter(
            $packer,
            $adapter,
            ShellOutputReqHandler::class,
            $request
        );
    }


    protected function logThrowable(\Throwable $throwable): void
    {
        $this->expHandler->report($throwable);
    }

}