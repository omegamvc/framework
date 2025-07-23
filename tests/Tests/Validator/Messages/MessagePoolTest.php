<?php

declare(strict_types=1);

namespace Tests\Validator\Messages;

use Omega\Validator\Messages\Message;
use Omega\Validator\Messages\MessagePool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test it can add messages using __get and field methods in the message pool.
 *
 * @return void
 */
#[CoversClass(Message::class)]
#[CoversClass(MessagePool::class)]
#[CoversClass(Validator::class)]
final class MessagePoolTest extends TestCase
{
    /**
     * Test it can add message using method __get.
     *
     * @return void
     */
    public function testItCanAddMessageUsingMethodGet(): void
    {
        $v = new MessagePool();
        $v->test->required = 'test';

        $m = new Message();
        $m->required = 'test';

        $this->assertEquals($m, $v->Messages()['test']);
    }

    /**
     * Test it can add message using method field.
     *
     * @return void
     */
    public function testItCanAddMessageUsingMethodField(): void
    {
        $v = new MessagePool();
        $v->field('test')->required = 'test';

        $m = new Message();
        $m->required = 'test';

        $this->assertEquals($m, $v->Messages()['test']);
    }
}
