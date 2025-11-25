<?php

namespace PageTemplateManager;

use BadMethodCallException;
use PageTemplateManager\Exceptions\TemplateFileNotFoundException;
use PageTemplateManager\Traits\Singleton;

/**
 * @method void autoDetectTypeTemplate(?array $valuesToPass = []) 
 * Will autodetect a template with a Type,
 * based on current page url and config, 
 * just replace 'Type' to whatever type you need
 */
class Manager
{
    use Singleton;

    /**
     * @param string $currentUrl 
     * @param Templater $templater
     * @param string|array $config
     */
    public function __construct(string $currentUrl, Templater $templater, $config = []) {
        $this->currentUrl = $currentUrl;
        $this->templater = $templater;
        
        if (is_string($config))
            $this->config = require $config;
        else if (is_array($config))
            $this->config = $config;
        else
            throw new \InvalidArgumentException(sprintf('$config parameter type must be one of these: array, string, found: %s', gettype($config)));
    }

    protected string $currentUrl;
    protected Templater $templater;
    protected array $config;

    public function __call($name, $arguments) {
        $regex = '/autoDetect(?<type>.*?)Template/';

        // Method should be autoDetect{Type}Template
        if (!preg_match($regex, $name, $matches))
            throw new BadMethodCallException(sprintf('Method does not exist or does not match pattern \'autoDetect{Type}Template\', found: %s', $name));

        // Too many arguments
        if (count($arguments) > 1)
            throw new BadMethodCallException(sprintf('Expected exactly 0 or 1 arguments, got %s', count($arguments)));

        // if first argument provided, it should be values
        if(isset($arguments[0]) && gettype($arguments[0]) !== 'array')
            throw new BadMethodCallException(sprintf('Expected argument 1 to be type "array", got %s', gettype($arguments[0])));

        $type = $matches['type'];
        $values = $arguments[0] ?? [];
        $name = $this->resolveTemplateName() ?? '';

        call_user_func([$this->templater, sprintf('load%sTemplate', $type)], $name, $values);
    }

    protected function resolveTemplateName() : ?string {
        $name = null;
        
        foreach (array_reverse($this->config) as $item) {
            foreach ($item['urls'] as $pattern) {

                if (preg_match("#^$pattern$#", $this->currentUrl)) {
                    $name = $item['name'];
                    break 2;
                }
            }
        }

        return $name;
    } 
}