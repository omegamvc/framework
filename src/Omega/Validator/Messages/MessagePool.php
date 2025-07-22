<?php

declare(strict_types=1);

namespace Omega\Validator\Messages;

/**
 * @property Message $test
 */
final class MessagePool
{
    /** @var Message[] */
    private array $messages = [];

    /**
     * Field tobe set.
     *
     * @param string $name field name
     * @return Message
     */
    public function __get(string $name): Message
    {
        return $this->field($name);
    }

    /**
     * Field tobe set.
     *
     * @param string $field   Field name
     * @param Message $message Message for this field
     * @return void
     */
    public function __set(string $field, Message $message): void
    {
        $this->messages[$field] = $message;
    }

    /**
     * Add message.
     *
     * @param string  $field   Field name
     * @param Message $message Message for this field
     * @return self
     */
    public function set(string $field, Message $message): self
    {
        $this->messages[$field] = $message;

        return $this;
    }

    /**
     * Field tobe set.
     *
     * @param string $name field name
     * @return Message
     */
    public function field(string $name): Message
    {
        return $this->messages[$name] = new Message();
    }

    /**
     * @return Message[]
     */
    public function Messages(): array
    {
        return $this->messages;
    }
}
