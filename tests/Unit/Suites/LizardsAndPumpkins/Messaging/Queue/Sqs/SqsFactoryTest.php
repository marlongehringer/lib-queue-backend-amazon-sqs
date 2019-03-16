<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Sqs;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SqsFactoryTest extends TestCase
{
    /**
     * @var SqsFactory
     */
    private $sqsFactory;

    /**
     * @var MasterFactory|MockObject
     */
    private $masterFactoryMock;

    protected function setUp()
    {
        $this->sqsFactory = new SqsFactory();

        $this->masterFactoryMock = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(['createConfigReader'])->getMockForAbstractClass();

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->method('get')->willReturnMap([
            ['AWS_SQS_COMMAND_QUEUE_URL', 'arn:aws:sqs:eu-central-1:311520829372:lap-sqs-test-command'],
            ['AWS_SQS_EVENT_QUEUE_URL', 'arn:aws:sqs:eu-central-1:311520829372:lap-sqs-test-event'],
            ['AWS_REGION', 'eu-central-1'],
            ['AWS_KEY', 'MY_AWS_KEY'],
            ['AWS_SECRET', 'MY_AWS_SECRET'],
        ]);
        $this->masterFactoryMock->method('createConfigReader')->willReturn($configReader);

        $this->sqsFactory->setMasterFactory($this->masterFactoryMock);
    }

    public function testImplementsFactory(): void
    {
        $this->assertInstanceOf(Factory::class, $this->sqsFactory);
    }

    public function testImplementsMessageQueueFactoryInterface(): void
    {
        $this->assertInstanceOf(MessageQueueFactory::class, $this->sqsFactory);
    }

    public function testCreateEventMessageQueue(): void
    {
        $this->assertInstanceOf(Queue::class, $this->sqsFactory->createEventMessageQueue());
    }

    public function testCreateCommandMessageQueue(): void
    {
        $this->assertInstanceOf(Queue::class, $this->sqsFactory->createCommandMessageQueue());
    }
}
