<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Sqs;

use Aws\Sqs\SqsClient;
use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class SqsFactory implements MessageQueueFactory, Factory
{
    use FactoryTrait;

    /**
     * @var SqsClient
     */
    private $sqsClient;

    public function createEventMessageQueue(): Queue
    {
        return new SqsQueue($this->getSqsClient(), $this->getEventQueueUrl());
    }

    public function createCommandMessageQueue(): Queue
    {
        return new SqsQueue($this->getSqsClient(), $this->getCommandQueueUrl());
    }

    private function getEventQueueUrl(): string
    {
        /** @var ConfigReader $configReader */
        /** @noinspection PhpUndefinedMethodInspection */
        $configReader = $this->getMasterFactory()->createConfigReader();

        return $configReader->get('AWS_SQS_EVENT_QUEUE_URL');
    }

    private function getCommandQueueUrl(): string
    {
        /** @var ConfigReader $configReader */
        /** @noinspection PhpUndefinedMethodInspection */
        $configReader = $this->getMasterFactory()->createConfigReader();

        return $configReader->get('AWS_SQS_COMMAND_QUEUE_URL');
    }

    private function getSqsClient(): SqsClient
    {
        if ($this->sqsClient) {
            return $this->sqsClient;
        }
        $credentials = $this->getAwsCredentials();

        $this->sqsClient = SqsClient::factory([
            'credentials' => $credentials,
            'region' => $this->getAwsRegion(),
        ]);

        return $this->sqsClient;
    }

    private function getAwsRegion(): string
    {
        /** @var ConfigReader $configReader */
        /** @noinspection PhpUndefinedMethodInspection */
        $configReader = $this->getMasterFactory()->createConfigReader();

        return $configReader->get('AWS_REGION');
    }

    private function getAwsCredentials(): array
    {
        /** @var ConfigReader $configReader */
        /** @noinspection PhpUndefinedMethodInspection */
        $configReader = $this->getMasterFactory()->createConfigReader();

        if ($configReader->has('AWS_KEY') && $configReader->has('AWS_SECRET')) {
            return [
                'key' => $configReader->get('AWS_KEY'),
                'secret' => $configReader->get('AWS_SECRET'),
            ];
        }

        return [];
    }
}
