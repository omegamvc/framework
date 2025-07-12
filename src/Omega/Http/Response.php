<?php

/**
 * Part of Omega - Http Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Http;

use InvalidArgumentException;
use Omega\View\View;

use function header;
use function is_null;
use function json_encode;
use function http_response_code;

/**
 * Response class.
 *
 * The `Response` class provides methods to build and send HTTP responses in an
 * Omega application.
 *
 * @category   Omega
 * @package    Http
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class Response
{
    /**
     * Redirect log level.
     *
     * This constant represents the log level for redirecting.
     * It can be used to indicate a log entry that involves a redirection process.
     *
     * @var string REDIRECT Holds the value for the redirect log level.
     */
    public const REDIRECT = 'REDIRECT';

    /**
     * HTML log level.
     *
     * This constant represents the log level for HTML messages.
     * It can be utilized for logging events or messages that are specifically related
     * to HTML content or responses.
     *
     * @var string HTML Holds the value for the HTML log level.
     */
    public const HTML = 'HTML';

    /**
     * JSON log level.
     *
     * This constant represents the log level for JSON messages.
     * It can be employed to log events or messages that are associated with JSON
     * data or responses.
     *
     * @var string JSON Holds the value for the JSON log level.
     */
    public const JSON = 'JSON';

    /**
     * Response type.
     *
     * @var string Holds the response type (default is HTML).
     */
    private string $type = 'HTML';

    /**
     * Response redirect.
     *
     * @var string|null Holds the response redirect URL or null.
     */
    private ?string $redirect = null;

    /**
     * Response content.
     *
     * @var string|View Holds the response content.
     */
    private string|View $content = '';

    /**
     * Status code.
     *
     * @var int Holds the HTTP status code (default is 200 OK).
     */
    private int $status = 200;

    /**
     * Headers array.
     *
     * @var array<string, string> Holds an array of custom HTTP headers for the response.
     */
    private array $headers = [];

    /**
     * Get or set the response content.
     *
     * @param string|View|null $content Holds the response content (optional).
     *
     * @return string|self|View|null Returns the content if no argument is provided, otherwise returns $this.
     */
    public function content(string|View|null $content = null): string|self|View|null
    {
        if (is_null($content)) {
            return $this->content;
        }

        $this->content = $content;

        return $this;
    }

    /**
     * Get or set the HTTP status code for the response.
     *
     * @param int|null $status Holds the HTTP status code (optional).
     *
     * @return int|$this Returns the status code if no argument is provided, otherwise returns $this.
     */
    public function status(?int $status = null): int|static
    {
        if (is_null($status)) {
            return $this->status;
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Add a custom HTTP header to the response.
     *
     * @param string $key   Holds the header key.
     * @param string $value Holds the header value.
     *
     * @return $this Returns $this for method chaining.
     */
    public function header(string $key, string $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Set the response to a redirect with the given URL.
     *
     * @param string|null $redirect Holds the URL to redirect to (optional).
     *
     * @return string|static|null Returns the redirect URL if no argument is provided, otherwise returns $this.
     */
    public function redirect(string $redirect = null): static|string|null
    {
        if (is_null($redirect)) {
            return $this->redirect;
        }

        $this->redirect = $redirect;
        $this->type     = static::REDIRECT;

        return $this;
    }

    /**
     * Set the response content type to JSON and provide JSON data.
     *
     * @param string|View $content Holds the JSON content to send.
     *
     * @return $this Returns $this for method chaining.
     */
    public function json(string|View $content): static
    {
        $this->content = $content;
        $this->type    = static::JSON;

        return $this;
    }

    /**
     * Get or set the response type (HTML, JSON, or REDIRECT).
     *
     * @param string|null $type Holds the response type (optional).
     *
     * @return string|$this Returns the response type if no argument is provided, otherwise returns $this.
     */
    public function type(?string $type = null): string|static
    {
        if (is_null($type)) {
            return $this->type;
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Send the HTTP response to the client.
     *
     * This method sends the response headers, status code, and content to the client.
     *
     * @return void
     */
    public function send(): void
    {
        foreach ($this->headers as $key => $value) {
            header($key . ':' . $value);
        }

        if ($this->type === static::HTML) {
            header('Content-Type: text/html');
            http_response_code($this->status);
            echo $this->content;

            return;
        }

        if ($this->type === static::JSON) {
            header('Content-Type: application/json');
            http_response_code($this->status);
            echo json_encode($this->content);

            return;
        }

        if ($this->type === static::REDIRECT) {
            header("Location: $this->redirect");

            return;
        }

        throw new InvalidArgumentException("$this->type is not a recognized type");
    }
}
