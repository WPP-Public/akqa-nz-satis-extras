<?php

namespace Heyday\Satis;

use Composer\Config;
use Composer\Repository\RepositoryManager;
use Composer\IO\IOInterface;
use Composer\Factory as ComposerFactory;

class Factory extends ComposerFactory
{
    protected function createRepositoryManager(IOInterface $io, Config $config)
    {
        $rm = parent::createRepositoryManager($io, $config);
        $rm->setRepositoryClass('satis-git', 'Heyday\Satis\Repository\SatisGitRepository');

        return $rm;
    }
}
