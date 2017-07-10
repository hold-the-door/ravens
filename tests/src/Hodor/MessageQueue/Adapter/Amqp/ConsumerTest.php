<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ConsumerTest as BaseConsumerTest;
use Hodor\MessageQueue\Adapter\Testing\Config;
use Hodor\MessageQueue\OutgoingMessage;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Consumer
 */
class ConsumerTest extends BaseConsumerTest
{
    /**
     * @var ChannelFactory[]
     */
    private $channel_factories;

    /**
     * @var Config
     */
    private $config;

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->channel_factories as $channel_factory) {
            $channel_factory->disconnectAll();
        }
    }

    /**
     * @param array $config_overrides
     * @return ConsumerInterface
     */
    protected function getTestConsumer(array $config_overrides = [])
    {
        $strategy_factory = $this->generateStrategyFactory($this->getTestConfig($config_overrides));
        $test_consumer = new Consumer('fast_jobs', $strategy_factory);

        return $test_consumer;
    }

    /**
     * @param OutgoingMessage $message
     */
    protected function produceMessage(OutgoingMessage $message)
    {
        $strategy_factory = $this->generateStrategyFactory($this->getTestConfig());
        $producer = new Producer('fast_jobs', $strategy_factory);

        $producer->produceMessage($message);
    }

    /**
     * @param Config $config
     * @return DeliveryStrategyFactory
     */
    private function generateStrategyFactory(Config $config)
    {
        $channel_factory = new ChannelFactory($config);

        $this->channel_factories[] = $channel_factory;

        return new DeliveryStrategyFactory($channel_factory);
    }

    /**
     * @param array $config_overrides
     * @return Config
     */
    private function getTestConfig(array $config_overrides = [])
    {
        if ($this->config) {
            return $this->config;
        }

        $config_provider = new ConfigProvider($this);
        $test_queues = $this->getTestQueues($config_provider);
        $this->config = $config_provider->getConfigAdapter($test_queues, $config_overrides);

        return $this->config;
    }

    /**
     * @param ConfigProvider $config_provider
     * @return array
     */
    private function getTestQueues(ConfigProvider $config_provider)
    {
        return [
            'fast_jobs' => $config_provider->getQueueConfig(),
        ];
    }
}
