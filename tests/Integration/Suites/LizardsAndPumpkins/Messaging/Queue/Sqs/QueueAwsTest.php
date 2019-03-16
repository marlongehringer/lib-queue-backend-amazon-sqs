<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Sqs;

use Aws\Sqs\SqsClient;
use Guzzle\Service\Resource\Model;
use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

class QueueAwsTest extends TestCase
{
    /**
     * @var SqsQueue
     */
    private $queue;

    /**
     * @var SqsClient
     */
    private $sqsClient;

    /**
     * @var string
     */
    private $queueUrl;

    protected function setUp()
    {
        $this->sqsClient = SqsClient::factory([
            'credentials' => [
                'key' => $_ENV['aws_key'],
                'secret' => $_ENV['aws_secret'],
            ],
            'region' => 'eu-central-1',
        ]);
        $queueName = str_replace('.', '-', uniqid('lap-sqs-test', true));

        /** @var Model $response */
        $response = $this->sqsClient->createQueue([
            'QueueName' => $queueName,
        ]);
        $this->queueUrl = $response->get('QueueUrl');
        $this->queue = new SqsQueue($this->sqsClient, $this->queueUrl);
    }

    protected function tearDown()
    {
        $this->sqsClient->deleteQueue([
            'QueueUrl' => $this->queueUrl,
        ]);
    }

    public function testEmptyAtBeginning()
    {
        $this->assertSame(0, $this->queue->count());
    }

    public function testCountRaisesAfterAddingAMessage()
    {
        $message = Message::withCurrentTime('my Message', [], []);
        $this->queue->add($message);
        $this->assertSame(1, $this->count());
    }

    public function testAddedMessageComesBack()
    {
        $message = Message::withCurrentTime('my Message', ['with complex payload'], ['and' => 'some metadata']);
        $this->queue->add($message);

        $receiver = new class implements MessageReceiver
        {
            /**
             * @var Message
             */
            public $message;

            public function receive(Message $message)
            {
                $this->message = $message;
            }
        };

        $this->queue->consume($receiver, 1);

        /** @var Message $returnedMessage */
        $returnedMessage = $receiver->message;
        $this->assertSame($message->serialize(), $returnedMessage->serialize());
    }

    public function testCountMessages()
    {
        $count = 4;
        for ($i = 0; $i < $count; $i++) {
            $this->queue->add(Message::withCurrentTime('name', [], []));
        }
        $this->assertSame($count, $this->queue->count());
    }

    public function testClear()
    {
        $count = 4;
        for ($i = 0; $i < $count; $i++) {
            $this->queue->add(Message::withCurrentTime('name', [], []));
        }
        $this->assertSame($count, $this->queue->count());
        $this->queue->clear();
        $this->assertSame(0, $this->queue->count());
    }
}
