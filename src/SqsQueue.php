<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Sqs;

use Aws\Sqs\SqsClient;
use Guzzle\Service\Resource\Model;
use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;

class SqsQueue implements Queue, Clearable
{
    /**
     * @var SqsClient
     */
    private $client;
    /**
     * @var string
     */
    private $queueUrl;

    public function __construct(SqsClient $client, string $queueUrl)
    {
        $this->client = $client;
        $this->queueUrl = $queueUrl;
    }

    /**
     * @return void
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }

    public function count(): int
    {
        /** @var Model $queueAttributes */
        $queueAttributes = $this->client->getQueueAttributes([
                'QueueUrl' => $this->queueUrl,
                'AttributeNames' => ['ApproximateNumberOfMessages'],
            ]
        );
        return $queueAttributes->get('Attributes')['ApproximateNumberOfMessages'];
    }

    /**
     * @param Message $message
     *
     * @return void
     */
    public function add(Message $message)
    {
        $this->client->sendMessage([
            'QueueUrl' => $this->queueUrl,
            'MessageBody' => $message->serialize(),
        ]);
    }

    public function consume(MessageReceiver $messageReceiver, int $numberOfMessagesToConsume)
    {
        // TODO: Implement consume() method.
    }
}
