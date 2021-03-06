<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Util\Config\EnvironmentConfigReader;

abstract class BaseCliCommand
{
    /**
     * @var CLImate
     */
    private $climate;
    
    final protected function setCLImate(CLImate $climate)
    {
        $this->climate = $climate;
    }

    final protected function getCLImate() : CLImate
    {
        if (null === $this->climate) {
            $this->setCLImate(new CLImate());
        }
        return $this->climate;
    }

    public function run()
    {
        try {
            $this->handleHookMethodFlow();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    private function handleHookMethodFlow()
    {
        $climate = $this->getCLImate();
        $this->prepareCommandLineArguments($climate);

        if ($this->getArg('help')) {
            $climate->usage();
        } else {
            $this->processBeforeExecute();
            $this->execute($climate);
            $this->processAfterExecute();
        }
    }
    
    private function handleException(\Exception $e)
    {
        $climate = $this->getCLImate();
        $climate->error($e->getMessage());
        $climate->error(sprintf('%s:%d', $e->getFile(), $e->getLine()));
        $climate->usage();
    }

    private function prepareCommandLineArguments(CLImate $climate)
    {
        $arguments = $this->getCommandLineArgumentsArray($climate);
        $climate->arguments->add($arguments);
        $climate->arguments->parse();
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate) : array
    {
        return [
            'environmentConfig' => [
                'prefix' => 'e',
                'longPrefix' => 'environmentConfig',
                'description' => 'Environment config settings, comma separated [foo=bar,baz=qux]',
            ],
            'help' => [
                'prefix' => 'h',
                'longPrefix' => 'help',
                'description' => 'Usage help',
                'noValue' => true
            ]
        ];
    }

    private function processBeforeExecute()
    {
        $env = $this->getArg('environmentConfig');
        if ($env) {
            $this->applyEnvironmentConfigSettings($env);
        }
        $this->beforeExecute($this->getCLImate());
    }

    protected function beforeExecute(CLImate $climate)
    {
        // Intentionally empty hook method
    }

    /**
     * @param CLImate $climate
     * @return void
     */
    abstract protected function execute(CLImate $climate);

    private function processAfterExecute()
    {
        $this->afterExecute($this->getCLImate());
    }

    protected function afterExecute(CLImate $climate)
    {
        // Intentionally empty hook method
    }

    /**
     * @param string $arg
     * @return bool|float|int|null|string
     */
    final protected function getArg(string $arg)
    {
        return $this->getCLImate()->arguments->get($arg);
    }

    /**
     * @param string $message
     * @return mixed
     */
    final protected function output(string $message)
    {
        return $this->getCLImate()->output($message);
    }

    private function applyEnvironmentConfigSettings(string $environmentConfigSettingsString)
    {
        every(explode(',', $environmentConfigSettingsString), function ($setting) {
            list($key, $value) = $this->parseSetting($setting);
            $_SERVER[EnvironmentConfigReader::ENV_VAR_PREFIX . strtoupper($key)] = trim($value);
        });
    }

    /**
     * @param string $setting
     * @return string[]
     */
    private function parseSetting(string $setting) : array
    {
        $this->validateSettingFormat($setting);
        return [$this->parseSettingKey($setting), $this->parseSettingValue($setting)];
    }

    private function parseSettingKey(string $setting) : string
    {
        $key = trim(substr($setting, 0, strpos($setting, '=')));
        if ('' === $key) {
            $message = sprintf('Environment settings have to be key=value pairs, key not found in "%s"', $setting);
            throw new \InvalidArgumentException($message);
        }
        return $key;
    }

    private function parseSettingValue(string $setting) : string
    {
        return substr($setting, strpos($setting, '=') + 1);
    }

    private function validateSettingFormat(string $setting)
    {
        if (false === strpos($setting, '=')) {
            $message = sprintf('Environment settings have to be key=value pairs, "=" not found in "%s"', $setting);
            throw new \InvalidArgumentException($message);
        }
    }
}
