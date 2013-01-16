<?php

namespace Heyday\Satis\Console;

use Heyday\Satis\Command;
use Heyday\Satis\Factory;
use Composer\Satis\Console\Application as SatisApplication;
use Composer\Command\Helper\DialogHelper;

class Application extends SatisApplication
{
    protected function registerCommands()
    {
        $this->add(new Command\BuildCommand);
        $this->add(new Command\ConfigInit);
        $this->add(new Command\ConfigAdd);
    }

    protected function getDefaultHelperSet()
    {
        $helperSet = parent::getDefaultHelperSet();

        $helperSet->set(new DialogHelper());

        return $helperSet;
    }

    public function getComposer($required = true, $config = null)
    {
        if (null === $this->composer) {
            try {
                $this->composer = Factory::create($this->io, $config);
            } catch (\InvalidArgumentException $e) {
                $this->io->write($e->getMessage());
                exit(1);
            }
        }

        return $this->composer;
    }
}
