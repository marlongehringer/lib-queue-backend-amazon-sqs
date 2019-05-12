<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Sqs;

use Aws\Sqs\SqsClient;
use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Sqs\Exception\MissingConfigurationException;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsNotSetException;
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

        try {
            return $configReader->get('AWS_SQS_EVENT_QUEUE_URL');
        } catch (\TypeError $e) {
            throw new MissingConfigurationException('Please pass AWS_SQS_EVENT_QUEUE_URL as env variable.');
        }
    }

    private function getCommandQueueUrl(): string
    {
        /** @var ConfigReader $configReader */
        /** @noinspection PhpUndefinedMethodInspection */
        $configReader = $this->getMasterFactory()->createConfigReader();

        try {
            return $configReader->get('AWS_SQS_COMMAND_QUEUE_URL');
        } catch (\TypeError $e) {
            throw new MissingConfigurationException('Please pass AWS_SQS_COMMAND_QUEUE_URL as env variable.');
        }
    }

    private function getSqsClient(): SqsClient
    {
        if ($this->sqsClient) {
            return $this->sqsClient;
        }

        try {
            $credentials = [
                'key' => $this->getAwsKey(),
                'secret' => $this->getAwsSecret(),
            ];
        } catch (EnvironmentConfigKeyIsNotSetException $e) {
            throw new MissingConfigurationException('Please pass credentials as env variable - check documentation how.');
        }

        if (! $this->getAwsKey() || ! $this->getAwsSecret()) {
            throw new MissingConfigurationException('Please pass credentials as env variable - check documentation how.');
        }

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
        try {
            return $configReader->get('AWS_REGION');
        } catch (\TypeError $e) {
            throw new MissingConfigurationException('Please pass AWS_REGION as env variable.');
        }
    }

    private function getAwsKey(): string
    {
        /** @var ConfigReader $configReader */
        /** @noinspection PhpUndefinedMethodInspection */
        $configReader = $this->getMasterFactory()->createConfigReader();

        return $configReader->get('AWS_KEY');
    }

    private function getAwsSecret(): string
    {
        /** @var ConfigReader $configReader */
        /** @noinspection PhpUndefinedMethodInspection */
        $configReader = $this->getMasterFactory()->createConfigReader();

        return $configReader->get('AWS_SECRET');
    }
}
