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

    public function clear(): void
    {
        $this->client->purgeQueue(['QueueUrl' => $this->queueUrl]);
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
    public function add(Message $message): void
    {
        $this->client->sendMessage([
            'QueueUrl' => $this->queueUrl,
            'MessageBody' => $message->serialize(),
        ]);
    }

    public function consume(MessageReceiver $messageReceiver, int $numberOfMessagesToConsume)
    {
        $messages = $this->client->receiveMessage([
            'QueueUrl' => $this->queueUrl,
            'WaitTimeSeconds' => 20,
            'MaxNumberOfMessages' => $numberOfMessagesToConsume,
        ])->get('Messages');

        foreach ($messages as $message) {
            $message = Message::rehydrate($message['Body']);
            $messageReceiver->receive($message);
            $numberOfMessagesToConsume--;
        }
    }
}
