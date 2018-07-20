<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\OutgoingMessage;
use PHPUnit\Framework\TestCase;

abstract class ProducerTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::produceMessage
     * @covers ::<private>
     */
    public function testMessageCanBeProduced()
    {
        $unique_message = 'hello ' . uniqid();

        $this->getTestProducer()->produceMessage(new OutgoingMessage($unique_message));

        $this->assertSame($unique_message, $this->consumeMessage());
    }

    /**
     * @covers ::__construct
     * @covers ::produceMessageBatch
     * @covers ::<private>
     */
    public function testMessagesCanBeBatchProduced()
    {
        $unique_messages = [
            'hello ' . uniqid(),
            'goodbye ' . uniqid(),
        ];

        $this->getTestProducer()->produceMessageBatch(array_map(function ($value) {
            return new OutgoingMessage($value);
        }, $unique_messages));

        foreach ($unique_messages as $unique_message) {
            $this->assertSame($unique_message, $this->consumeMessage());
        }
    }

    /**
     * @return ProducerInterface
     */
    abstract protected function getTestProducer();

    /**
     * @return string $expected_message
     */
    abstract protected function consumeMessage();
}
