<?php

declare(strict_types=1);

namespace Omega\Container\Compiler;

use ArrayIterator;
use Closure;
use Exception;
use InvalidArgumentException;
use Omega\Container\Definition\ArrayDefinition;
use Omega\Container\Definition\DecoratorDefinition;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\EnvironmentVariableDefinition;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\FactoryDefinition;
use Omega\Container\Definition\ObjectDefinition;
use Omega\Container\Definition\Reference;
use Omega\Container\Definition\Source\DefinitionSourceInterface;
use Omega\Container\Definition\StringDefinition;
use Omega\Container\Definition\ValueDefinition;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Proxy\ProxyFactoryInterface;
use Omega\SerializableClosure\Support\ReflectionClosure;
use ReflectionException;
use UnitEnum;

use function array_keys;
use function array_map;
use function chmod;
use function class_exists;
use function dirname;
use function file_exists;
use function file_put_contents;
use function implode;
use function is_array;
use function is_dir;
use function is_object;
use function is_resource;
use function is_string;
use function is_writable;
use function method_exists;
use function mkdir;
use function ob_get_clean;
use function ob_start;
use function preg_match;
use function rename;
use function rtrim;
use function sprintf;
use function tempnam;
use function trim;
use function unlink;
use function var_export;

use const PHP_VERSION_ID;

/**
 * Compiles the container into PHP code much more optimized for performances.
 */
class Compiler
{
    /** @var string */
    private string $containerClass;

    /** @var string */
    private string $containerParentClass;

    /**
     * Definitions indexed by the entry name. The value can be null if the definition needs to be fetched.
     *
     * Keys are strings, values are `Definition` objects or null.
     */
    private ArrayIterator $entriesToCompile;

    /**
     * Progressive counter for definitions.
     *
     * Each key in $entriesToCompile is defined as 'SubEntry' + counter
     * and each definition has always the same key in the CompiledContainer
     * if PHP-DI configuration does not change.
     */
    private int $subEntryCounter = 0;

    /**
     * Progressive counter for CompiledContainer get methods.
     *
     * Each CompiledContainer method name is defined as 'get' + counter
     * and remains the same after each recompilation
     * if PHP-DI configuration does not change.
     */
    private int $methodMappingCounter = 0;

    /**
     * Map of entry names to method names.
     *
     * @var string[]
     */
    private array $entryToMethodMapping = [];

    /**
     * @var string[]
     */
    private array $methods = [];

    /** @var bool  */
    private bool $autowiringEnabled;

    public function __construct(
        private readonly ProxyFactoryInterface $proxyFactory,
    ) {
    }

    public function getProxyFactory() : ProxyFactoryInterface
    {
        return $this->proxyFactory;
    }

    /**
     * Compile the container.
     *
     * @param DefinitionSourceInterface $definitionSource
     * @param string           $directory
     * @param string $className
     * @param string $parentClassName
     * @param bool $autowiringEnabled
     * @return string The compiled container file name.
     * @throws DependencyException
     * @throws InvalidDefinitionException
     */
    public function compile(
        DefinitionSourceInterface $definitionSource,
        string                    $directory,
        string                    $className,
        string                    $parentClassName,
        bool                      $autowiringEnabled,
    ) : string {
        $fileName = rtrim($directory, '/') . '/' . $className . '.php';

        if (file_exists($fileName)) {
            // The container is already compiled
            return $fileName;
        }

        $this->autowiringEnabled = $autowiringEnabled;

        // Validate that a valid class name was provided
        $validClassName = preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $className);
        if (!$validClassName) {
            throw new InvalidArgumentException(
                sprintf(
                    "The container cannot be compiled: `%s` is not a valid PHP class name",
                    $className
                )
            );
        }

        $this->entriesToCompile = new ArrayIterator($definitionSource->getDefinitions());

        // We use an ArrayIterator so that we can keep adding new items to the list while we compile entries
        foreach ($this->entriesToCompile as $entryName => $definition) {
            $silenceErrors = false;
            // This is an entry found by reference during autowiring
            if (!$definition) {
                $definition = $definitionSource->getDefinition($entryName);
                // We silence errors for those entries because type-hints may reference interfaces/abstract classes
                // which could later be defined, or even not used (we don't want to block the compilation for those)
                $silenceErrors = true;
            }
            if (!$definition) {
                // We do not throw a `NotFound` exception here because the dependency
                // could be defined at runtime
                continue;
            }
            // Check that the definition can be compiled
            $errorMessage = $this->isCompilable($definition);
            if ($errorMessage !== true) {
                continue;
            }
            try {
                $this->compileDefinition($entryName, $definition);
            } catch (InvalidDefinitionException $e) {
                if ($silenceErrors) {
                    // forget the entry
                    unset($this->entryToMethodMapping[$entryName]);
                } else {
                    throw $e;
                }
            }
        }

        $this->containerClass = $className;
        $this->containerParentClass = $parentClassName;

        ob_start();
        require __DIR__ . '/Template.php';
        $fileContent = ob_get_clean();

        $fileContent = "<?php\n" . $fileContent;

        $this->createCompilationDirectory(dirname($fileName));
        $this->writeFileAtomic($fileName, $fileContent);

        return $fileName;
    }

    /**
     * @param string $fileName
     * @param string $content
     * @return void
     */
    private function writeFileAtomic(string $fileName, string $content) : void
    {
        $tmpFile = @tempnam(dirname($fileName), 'swap-compile');
        if ($tmpFile === false) {
            throw new InvalidArgumentException(
                sprintf('Error while creating temporary file in %s', dirname($fileName))
            );
        }
        @chmod($tmpFile, 0666);

        $written = file_put_contents($tmpFile, $content);
        if ($written === false) {
            @unlink($tmpFile);

            throw new InvalidArgumentException(sprintf('Error while writing to %s', $tmpFile));
        }

        @chmod($tmpFile, 0666);
        $renamed = @rename($tmpFile, $fileName);
        if (!$renamed) {
            @unlink($tmpFile);

            throw new InvalidArgumentException(sprintf('Error while renaming %s to %s', $tmpFile, $fileName));
        }
    }

    /**
     * @param string $entryName
     * @param DefinitionInterface $definition
     * @return string The method name
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws Exception
     */
    private function compileDefinition(string $entryName, DefinitionInterface $definition) : string
    {
        // Generate a unique method name
        $methodName = 'get' . (++$this->methodMappingCounter);
        $this->entryToMethodMapping[$entryName] = $methodName;

        switch (true) {
            case $definition instanceof ValueDefinition:
                $value = $definition->getValue();
                $code = 'return ' . $this->compileValue($value) . ';';
                break;
            case $definition instanceof Reference:
                $targetEntryName = $definition->getTargetEntryName();
                $code = 'return $this->delegateContainer->get(' . $this->compileValue($targetEntryName) . ');';
                // If this method is not yet compiled we store it for compilation
                if (!isset($this->entriesToCompile[$targetEntryName])) {
                    $this->entriesToCompile[$targetEntryName] = null;
                }
                break;
            case $definition instanceof StringDefinition:
                $entryName = $this->compileValue($definition->getName());
                $expression = $this->compileValue($definition->getExpression());
                $code = 'return \Omega\Container\Definition\StringDefinition::resolveExpression(' . $entryName . ', ' . $expression . ', $this->delegateContainer);';
                break;
            case $definition instanceof EnvironmentVariableDefinition:
                $variableName = $this->compileValue($definition->variableName);
                $isOptional = $this->compileValue($definition->isOptional);
                $defaultValue = $this->compileValue($definition->defaultValue);
                $code = <<<PHP
                            \$value = \$_ENV[$variableName] ?? \$_SERVER[$variableName] ?? getenv($variableName);
                            if (false !== \$value) return \$value;
                            if (!$isOptional) {
                                throw new \Omega\Container\Definition\Exceptions\InvalidDefinitionException("The environment variable '{$definition->variableName}' has not been defined");
                            }
                            return $defaultValue;
                    PHP;
                break;
            case $definition instanceof ArrayDefinition:
                try {
                    $code = 'return ' . $this->compileValue($definition->getValues()) . ';';
                } catch (Exception $e) {
                    throw new DependencyException(sprintf(
                        'Error while compiling %s. %s',
                        $definition->getName(),
                        $e->getMessage()
                    ), 0, $e);
                }
                break;
            case $definition instanceof ObjectDefinition:
                $compiler = new ObjectCreationCompiler($this);
                $code = $compiler->compile($definition);
                $code .= "\n        return \$object;";
                break;
            case $definition instanceof DecoratorDefinition:
                $decoratedDefinition = $definition->getDecoratedDefinition();
                if (! $decoratedDefinition instanceof DefinitionInterface) {
                    if (! $definition->getName()) {
                        throw new InvalidDefinitionException('Decorators cannot be nested in another definition');
                    }
                    throw new InvalidDefinitionException(sprintf(
                        'Entry "%s" decorates nothing: no previous definition with the same name was found',
                        $definition->getName()
                    ));
                }
                $code = sprintf(
                    'return call_user_func(%s, %s, $this->delegateContainer);',
                    $this->compileValue($definition->getCallable()),
                    $this->compileValue($decoratedDefinition)
                );
                break;
            case $definition instanceof FactoryDefinition:
                $value = $definition->getCallable();

                // Custom error message to help debugging
                $isInvokableClass = is_string($value) && class_exists($value) && method_exists($value, '__invoke');
                if ($isInvokableClass && !$this->autowiringEnabled) {
                    throw new InvalidDefinitionException(sprintf(
                        'Entry "%s" cannot be compiled. Invokable classes cannot be automatically resolved if'.
                        'autowiring is disabled on the container, you need to enable autowiring or define the entry'
                        .'manually.',
                        $entryName
                    ));
                }

                $definitionParameters = '';
                if (!empty($definition->getParameters())) {
                    $definitionParameters = ', ' . $this->compileValue($definition->getParameters());
                }

                $code = sprintf(
                    'return $this->resolveFactory(%s, %s%s);',
                    $this->compileValue($value),
                    var_export($entryName, true),
                    $definitionParameters
                );

                break;
            default:
                // This case should not happen (so it cannot be tested)
                throw new Exception('Cannot compile definition of type ' . $definition::class);
        }

        $this->methods[$methodName] = $code;

        return $methodName;
    }

    /**
     * @param mixed $value
     * @return string
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws ReflectionException
     */
    public function compileValue(mixed $value) : string
    {
        // Check that the value can be compiled
        $errorMessage = $this->isCompilable($value);
        if ($errorMessage !== true) {
            throw new InvalidDefinitionException($errorMessage);
        }

        if ($value instanceof DefinitionInterface) {
            // Give it an arbitrary unique name
            $subEntryName = 'subEntry' . (++$this->subEntryCounter);
            // Compile the sub-definition in another method
            $methodName = $this->compileDefinition($subEntryName, $value);

            // The value is now a method call to that method (which returns the value)
            return "\$this->$methodName()";
        }

        if (is_array($value)) {
            $value = array_map(function ($value, $key) {
                $compiledValue = $this->compileValue($value);
                $key = var_export($key, true);

                return "            $key => $compiledValue,\n";
            }, $value, array_keys($value));
            $value = implode('', $value);

            return "[\n$value        ]";
        }

        if ($value instanceof Closure) {
            return $this->compileClosure($value);
        }

        return var_export($value, true);
    }

    /**
     * @param string $directory
     * @return void
     */
    private function createCompilationDirectory(string $directory) : void
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Compilation directory does not exist and cannot be created: %s.',
                    $directory
                )
            );
        }
        if (!is_writable($directory)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Compilation directory is not writable: %s.',
                    $directory
                )
            );
        }
    }

    /**
     * @return string|true If true is returned that means that the value is compilable.
     */
    private function isCompilable($value) : string|bool
    {
        if ($value instanceof ValueDefinition) {
            return $this->isCompilable($value->getValue());
        }
        if (($value instanceof DecoratorDefinition) && empty($value->getName())) {
            return 'Decorators cannot be nested in another definition';
        }
        // All other definitions are compilable
        if ($value instanceof DefinitionInterface) {
            return true;
        }
        if ($value instanceof Closure) {
            return true;
        }
        if ((PHP_VERSION_ID >= 80100) && ($value instanceof UnitEnum)) {
            return true;
        }
        if (is_object($value)) {
            return 'An object was found but objects cannot be compiled';
        }
        if (is_resource($value)) {
            return 'A resource was found but resources cannot be compiled';
        }

        return true;
    }

    /**
     * @throws InvalidDefinitionException
     * @throws ReflectionException
     */
    private function compileClosure(Closure $closure) : string
    {
        $reflector = new ReflectionClosure($closure);

        if ($reflector->getUseVariables()) {
            throw new InvalidDefinitionException(
                'Cannot compile closures which import variables using the `use` keyword'
            );
        }

        if ($reflector->isBindingRequired() || $reflector->isScopeRequired()) {
            throw new InvalidDefinitionException(
                'Cannot compile closures which use $this or self/static/parent references'
            );
        }

        // Force all closures to be static (add the `static` keyword), i.e. they can't use
        // $this, which makes sense since their code is copied into another class.
        $code = ($reflector->isStatic() ? '' : 'static ') . $reflector->getCode();

        return trim($code, "\t\n\r;");
    }
}
