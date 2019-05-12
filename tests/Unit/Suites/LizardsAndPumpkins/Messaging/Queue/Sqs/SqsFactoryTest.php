<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Sqs;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Sqs\Exception\MissingConfigurationException;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Sqs\SqsFactory
 */
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

    final protected function setUp(): void
    {
        $this->sqsFactory = new SqsFactory();

        $this->masterFactoryMock = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(['createConfigReader'])->getMockForAbstractClass();

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
        $this->prepareConfigReader();
        $this->assertInstanceOf(Queue::class, $this->sqsFactory->createEventMessageQueue());
    }

    public function testCreateCommandMessageQueue(): void
    {
        $this->prepareConfigReader();
        $this->assertInstanceOf(Queue::class, $this->sqsFactory->createCommandMessageQueue());
    }

    public function testThrowsExceptionIfNoAwsArea()
    {
        $this->expectException(MissingConfigurationException::class);
        $this->expectExceptionMessage('Please pass AWS_REGION as env variable.');

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->method('get')->willReturnMap([
            ['AWS_KEY', 'MY_AWS_KEY'],
            ['AWS_SECRET', 'MY_AWS_SECRET'],
        ]);
        $this->masterFactoryMock->method('createConfigReader')->willReturn($configReader);

        $this->assertInstanceOf(Queue::class, $this->sqsFactory->createCommandMessageQueue());
    }

    public function testThrowsExceptionIfNoEventQueueUrl()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please pass AWS_SQS_EVENT_QUEUE_URL as env variable.');

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->method('get')->willReturnMap([
            ['AWS_REGION', 'eu-central-1'],
            ['AWS_KEY', 'MY_AWS_KEY'],
            ['AWS_SECRET', 'MY_AWS_SECRET'],
        ]);
        $this->masterFactoryMock->method('createConfigReader')->willReturn($configReader);

        $this->assertInstanceOf(Queue::class, $this->sqsFactory->createEventMessageQueue());
    }

    public function testThrowsExceptionIfNoCredentials()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please pass credentials as env variable - check documentation how.');

        $configReader = $this->createMock(ConfigReader::class);
        $this->masterFactoryMock->method('createConfigReader')->willReturn($configReader);

        $this->assertInstanceOf(Queue::class, $this->sqsFactory->createCommandMessageQueue());
    }

    public function testThrowsExceptionIfNoCommandQueueUrl()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please pass AWS_SQS_COMMAND_QUEUE_URL as env variable.');

        $configReader = $this->createMock(ConfigReader::class);
        $configReader->method('get')->willReturnMap([
            ['AWS_REGION', 'eu-central-1'],
            ['AWS_KEY', 'MY_AWS_KEY'],
            ['AWS_SECRET', 'MY_AWS_SECRET'],
        ]);
        $this->masterFactoryMock->method('createConfigReader')->willReturn($configReader);

        $this->assertInstanceOf(Queue::class, $this->sqsFactory->createCommandMessageQueue());
    }

    private function prepareConfigReader(): void
    {
        $configReader = $this->createMock(ConfigReader::class);
        $configReader->method('get')->willReturnMap([
            ['AWS_SQS_COMMAND_QUEUE_URL', 'arn:aws:sqs:eu-central-1:311520829372:lap-sqs-test-command'],
            ['AWS_SQS_EVENT_QUEUE_URL', 'arn:aws:sqs:eu-central-1:311520829372:lap-sqs-test-event'],
            ['AWS_REGION', 'eu-central-1'],
            ['AWS_KEY', 'MY_AWS_KEY'],
            ['AWS_SECRET', 'MY_AWS_SECRET'],
        ]);
        $this->masterFactoryMock->method('createConfigReader')->willReturn($configReader);
    }
}
