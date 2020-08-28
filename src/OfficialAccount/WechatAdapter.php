<?php


/**
 * Class WechatAdapter
 * @package Commune\Platform\Wechat\OfficialAccount
 */

namespace Commune\Platform\Wechat\OfficialAccount;


use Commune\Contracts\Cache;
use Psr\Log\LoggerInterface;
use Commune\Contracts\Log\ExceptionReporter;
use Commune\Hyperf\Support\HttpBabel;
use Commune\Platform\Wechat\Contracts\MessageBabel;
use Commune\Platform\Wechat\Contracts\WechatMessage;
use Commune\Protocals\HostMsg;
use Commune\Blueprint\Framework\ReqContainer;
use Commune\Blueprint\Kernel\Protocals\AppRequest;
use Commune\Blueprint\Kernel\Protocals\AppResponse;
use Commune\Blueprint\Platform\Adapter;
use Commune\Kernel\Protocals\IShellInputRequest;
use Commune\Message\Host\Convo\IEventMsg;
use Commune\Message\Host\Convo\IText;
use Commune\Message\Host\Convo\IUnsupportedMsg;
use Commune\Message\Intercom\IInputMsg;
use Commune\Platform\Wechat\Constants\EventTypes;
use Commune\Platform\Wechat\Constants\MessageTypes;
use Commune\Platform\Wechat\Shell\Messages\VoiceRecognitionMsg;
use Commune\Platform\Wechat\Shell\Messages\WechatAudioMsg;
use Commune\Protocals\IntercomMsg;
use EasyWeChat\Kernel\Contracts\MessageInterface;
use EasyWeChat\OfficialAccount\Application as Wechat;
use EasyWeChat\Kernel\Messages as EasyWechatMessages;
use Commune\Blueprint\Kernel\Protocals\ShellOutputResponse;

class WechatAdapter implements Adapter
{
    const QUERY_SCENE_FIELD = 'scene';

    /**
     * @var WechatPacker
     */
    protected $packer;

    /**
     * @var string
     */
    protected $appId;


    /**
     * @var AppRequest|null
     */
    protected $_request;

    /**
     * @var array|null
     */
    protected $userData;

    /**
     * WechatAdapter constructor.
     * @param WechatPacker $packer
     * @param string $appId
     */
    public function __construct(WechatPacker $packer, string $appId)
    {
        $this->packer = $packer;
        $this->appId = $appId;
    }


    public function isInvalidRequest(): ? string
    {
        if (isset($this->packer->appRequest)) {
            return null;
        }

        return $this->packer->isInvalidInput()
            ?? $this->createRequest();
    }

    public function getOfficialAccountConfig() : OfficialAccountPlatformConfig
    {
        return $this->packer->reqContainer->make(OfficialAccountPlatformConfig::class);
    }


    protected function getWechat() : Wechat
    {
        return $this->packer->reqContainer->make(Wechat::class);
    }

    protected function createRequest() : ? string
    {
        if (isset($this->_request)) {
            return null;
        }

        $container = $this->packer->reqContainer;
        /**
         * @var Wechat $wechat
         */
        $wechat = $this->getWechat();
        $message = $wechat->server->getMessage();
        $hostMsg = $this->parseHostMessage($message);
        $openId = $message['FromUserName'];


        $logger = $container->make(LoggerInterface::class);
        if (empty($openId)) {
            $logger->error("empty wechat openId: ". json_encode($message));
            return "empty wechat openId";
        }


        $scene = $this->getScene();

        $sessionId = $this->makeSessionId($openId);
        $userName = $this->fetchUserData(
            $container,
            $wechat,
            $openId,
            $logger
        );

        $input = IInputMsg::instance(
            $hostMsg,
            $sessionId,
            $openId,
            $userName,
            '',
            null,
            $this->packer->reqContainer->getId(),
            $scene
        );

        $config = $this->getOfficialAccountConfig();
        $entry = $config->entries[$scene] ?? '';

        $this->_request = IShellInputRequest::instance(
            false,
            $input,
            $entry,
            $this->makeEnv(),
            null,
            $container->getId()
        );

        return null;
    }

    protected function makeEnv() : array
    {
        return [];
    }

    protected function makeSessionId(string $openId) : string
    {
        $shellId = $this->packer->shell->getId();
        return sha1("commune:shell:$shellId:openId:$openId");
    }

    protected function fetchUserData(
        ReqContainer $container,
        Wechat $wechat,
        string $openId,
        LoggerInterface $logger
    ) : array
    {
        try {
            if (isset($this->userData)) {
                return $this->userData;
            }
            /**
             * @var Cache $cache
             */
            $cache = $container->make(Cache::class);
            $key = "wechat:user:$openId";

            if ($cache->has($key)) {
                $data = $cache->get($key);
                if (is_string($data)) {
                    $array = unserialize($data);
                    if (is_array($array)) {
                        return $array;
                    }
                }
            }


            $user = $wechat->user->get($openId);
            $serialized = serialize($user);
            $cache->set($key, $serialized, 3600);

            return $user;

        } catch (\Exception $e) {
            $logger->error($e);
            return [];
        }

    }

    protected function parseHostMessage($message) : HostMsg
    {
        $msgType = $message['MsgType'] ?? '';

        // 系统默认的 message 处理.
        switch ($msgType) {

            case MessageTypes::TEXT :
                return IText::instance($message['Context'] ?? '');

            case MessageTypes::EVENT:
                $event = $message['Event'] ?? '';
                if ( $event === EventTypes::SUBSCRIBE) {

                    return IEventMsg::instance(HostMsg\DefaultEvents::EVENT_CLIENT_CONNECTION);
                }

                return IEventMsg::instance($message['Event'] ?? HostMsg\DefaultEvents::EVENT_CLIENT_ACKNOWLEDGE);

            case MessageTypes::VOICE :
                $id = $message['MediaId'] ?? '';
                $recognition = $message['Recognition'] ?? null;
                // 加入语音识别.
                if (isset($recognition)) {
                    return VoiceRecognitionMsg::instance($id, $recognition);
                } else {
                    return WechatAudioMsg::instance($id);
                }

            // case MessageTypes::IMAGE :
            //     $id = $this->input['MediaId'] ?? '';
            //     $url = $this->input['PicUrl'] ?? '';

            default :
                return IUnsupportedMsg::instance($msgType);
        }

    }


    public function getRequest(): AppRequest
    {
        if (isset($this->_request)) {
            return $this->_request;
        }

        if (isset($this->packer->appRequest)) {
            return $this->_request = $this->packer->appRequest;
        }

        $this->createRequest();
        return $this->_request;
    }

    protected function getScene() : string
    {
        return $this->packer->request->get[self::QUERY_SCENE_FIELD] ?? '';
    }

    /**
     * @param ShellOutputResponse $response
     */
    public function sendResponse(AppResponse $response): void
    {
        $messages = $response->getOutputs();
        // 由于微信公众号现在不允许单个请求多个回复, 所以只好区别对待.
        if (count($messages) === 1) {
            $message = current($messages);
            $reply = $this->renderSingleMessage($message);
        } else {
            $reply = $this->renderMultipleMessages($messages);
        }

        $wechat = $this->getWechat();

        try {

            $wechat->server->push(function() use ($reply): ? MessageInterface {
                return $reply;
            });
            $response = $wechat->server->serve();
            HttpBabel::sendResponseFromSymfonyToSwoole(
                $response,
                $this->packer->response,
                false
            );

        } catch (\Throwable $e) {
            /**
             * @var ExceptionReporter $reporter
             */
            $reporter = $this
                ->packer
                ->reqContainer
                ->make(ExceptionReporter::class);

            $reporter->report($e);
            $this->packer->fail($e->getMessage());
        }
    }

    /**
     * @param IntercomMsg $message
     * @return MessageInterface|null
     */
    protected function renderSingleMessage(IntercomMsg $message) : ? MessageInterface
    {
        $container = $this->packer->reqContainer;
        // 如果实现了 message babel
        if ($container->has(MessageBabel::class)) {
            /**
             * @var MessageBabel $babel
             */
            $babel = $container->make(MessageBabel::class);
            return $babel->transform($message->getMessage());
        }

        $msg = $message->getMessage();

        if ($msg instanceof HostMsg\Convo\VerbalMsg) {
            return new EasyWechatMessages\Text($msg->getText());

        } elseif ($msg instanceof WechatMessage) {
            return $msg->toEasyWechatMessage();

        } else {
            $text = $this->getOfficialAccountConfig()->unsupportedReply;
            return new EasyWechatMessages\Text($text);
        }
    }


    /**
     * @param array $messages
     * @return MessageInterface|null
     */
    protected function renderMultipleMessages(array $messages) : ? MessageInterface
    {
        /**
         * @var HostMsg[] $msgs
         */
        $msgs = array_map(function(IntercomMsg $message) : HostMsg {
            return $message->getMessage();
        }, $messages);

        $text = [];

        foreach ($msgs as $message) {
            if ($message instanceof HostMsg\Convo\VerbalMsg) {
                $text[] = $message->getText();
            }
        }
        return new EasyWechatMessages\Text(implode("\n\n", $text));
    }




    public function destroy(): void
    {
        unset(
            $this->_request,
            $this->packer,
            $this->userData
        );
    }


}