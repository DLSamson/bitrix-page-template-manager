<?php

namespace PageTemplateManager;

use BadMethodCallException;
use Illuminate\Support\Str;
use PageTemplateManager\Traits\Singleton;
use PageTemplateManager\Exceptions\TemplateFileNotFoundException;
use function str_ends_with;

class Templater
{
    use Singleton;

    /**
     * @param string $templateDir   pathToFolder with all templates
     * @param mixed $globalVariablesToPass     variables to pass into template
     */
    public function __construct(string $templateDir, array $globalVariablesToPass = []) {
        $this->templateDir = str_ends_with($templateDir, '/') // If possible, we use polyfill
            ? substr($templateDir, 0, -1)
            : $templateDir;

        $this->globalVariablesToPass = $globalVariablesToPass;
    }

    protected string $templateDir;
    protected array $globalVariablesToPass = [];

    /**
     * @throws TemplateFileNotFoundException
     * @throws BadMethodCallException
     */
    public function __call($name, $arguments) {
        $regex = '/load(?<type>.*?)Template/';

        // Expect load{Type}Template method pattern
        if (!preg_match($regex, $name, $matches))
            throw new BadMethodCallException(sprintf('Method does not exsits or does not match pattern \'load{Type}Template\', found: %s', $name));

        // Too many args
        if (count($arguments) > 2)
            throw new BadMethodCallException(sprintf('Expected exactly 0, 1 or 2 arguments, got %s', count($arguments)));

        // If first argument is presented, it should be a name
        if(isset($arguments[0]) && gettype($arguments[0]) !== 'string')
            throw new BadMethodCallException(sprintf('Expected argument 1 to be type "string", got %s', gettype($arguments[0])));

        // if second argument is presented, it should be values
        if(isset($arguments[1]) && gettype($arguments[1]) !== 'array')
            throw new BadMethodCallException(sprintf('Expected argument 2 to be type "array", got %s', gettype($arguments[1])));

        $name = $arguments[0] ?? '';
        $values = $arguments[1] ?? [];
        $type = Str::camel($matches['type']);

        $this->loadTemplate($name, $type, $values);
    }

    /**
     * @throws TemplateFileNotFoundException
     */
    public function loadTemplate(string $name, string $type, array $values = []) : void {

        $pathToTemplateFile = $name 
            ? sprintf('%s.%s.php', $this->getPathToFile($name), $type) // loadTypeTemplate($name <-- provided)
            : sprintf('%s%s.php', $this->getPathToFile($name), $type); // loadTypeTemplate($name <-- NOT provided or empty)

        // In case name was not provided, it will be something like `pathToTemplateDir/type.php`
        if (!file_exists($pathToTemplateFile))
            throw new TemplateFileNotFoundException(sprintf('Template file with name %s and type %s not found at %s', $name ?: '*empty*', $type, $pathToTemplateFile));

        $this->includeTemplateFile($pathToTemplateFile, $values);
    }

    protected function getPathToFile(string $name) : string {
        $pathParts = explode('.', $name);
        return $this->templateDir . '/' . implode('/', $pathParts);
    }
    
    protected function includeTemplateFile(string $file, array $values = []): void {
        global $APPLICATION, $USER, $DB;

        (function (string $file, array $varsToPass, array $values) use ($APPLICATION, $USER, $DB) {
            extract($varsToPass);
            extract($values);
            require $file;
        })($file, $this->globalVariablesToPass, $values);
    }
}