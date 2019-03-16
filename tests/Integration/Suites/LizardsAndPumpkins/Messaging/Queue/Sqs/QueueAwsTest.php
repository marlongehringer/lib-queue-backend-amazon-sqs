<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Sqs;

use Aws\Sqs\SqsClient;
use Guzzle\Service\Resource\Model;
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
}
