<?php

/**
 * Part of Omega - Config Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Config\Source;

use Exception;
use Omega\Config\Exceptions\MalformedXmlException;

use function json_decode;
use function json_encode;
use function simplexml_load_string;

/**
 * Configuration source that loads data from an XML file.
 *
 * This implementation reads an XML configuration file, parses its content, and
 * returns it as an associative array. It ensures the file is readable and properly
 * structured.
 *
 * @category   Omega
 * @package    Config
 * @subpackage Source
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class XmlConfig extends AbstractFile
{
    /**
     * {@inheritdoc}
     *
     * @throws MalformedXmlException If unable to produce the content.
     */
    public function fetch(): array
    {
        try {
            $xml = simplexml_load_string($this->fetchContent());
            return json_decode(json_encode($xml), true) ?? [];
        } catch (Exception $_) {
            throw new MalformedXmlException('Invalid XML format in configuration file.');
        }
    }
}
