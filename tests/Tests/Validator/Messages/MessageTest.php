<?php

declare(strict_types=1);

namespace Tests\Validator\Messages;

use Omega\Validator\Messages\Message;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test it can add, list, and get messages properly.
 *
 * @return void
 */
#[CoversClass(Message::class)]
#[CoversClass(Validator::class)]
final class MessageTest extends TestCase
{
    /**
     * Test it can add a message using __get magic property.
     *
     * @return void
     */
    public function testItCanAddMessageUsingGet(): void
    {
        $message = new Message();
        $message->required = 'test';

        $this->assertEquals([
            'required' => 'test',
        ], $message->messages());
    }

    /**
     * Test it can add a message by setting it as an array element.
     *
     * @return void
     */
    public function testItCanAddMessageUsingArraySet(): void
    {
        $message = new Message();
        $message['required'] = 'test';

        $this->assertEquals([
            'required' => 'test',
        ], $message->messages());
    }

    /**
     * Test it can list multiple messages.
     *
     * @return void
     */
    public function testItCanListMessages(): void
    {
        $message = new Message();
        $message->required = 'test';
        $message->alpha = 'test';

        $this->assertEquals([
            'required' => 'test',
            'alpha' => 'test',
        ], $message->messages());
    }

    /**
     * Test it can get a message using array access.
     *
     * @return void
     */
    public function testItCanGetMessageUsingArrayAccess(): void
    {
        $message = new Message();
        $message['required'] = 'test';

        $this->assertSame('test', $message['required']);
    }
}
