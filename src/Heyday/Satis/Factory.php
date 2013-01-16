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
        $rm = new RepositoryManager($io, $config);
        $rm->setRepositoryClass('composer', 'Composer\Repository\ComposerRepository');
        $rm->setRepositoryClass('vcs', 'Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('package', 'Composer\Repository\PackageRepository');
        $rm->setRepositoryClass('pear', 'Composer\Repository\PearRepository');
        $rm->setRepositoryClass('git', 'Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('svn', 'Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('hg', 'Composer\Repository\VcsRepository');
        $rm->setRepositoryClass('satis-git', 'Heyday\Satis\Repository\SatisGitRepository');

        return $rm;
    }
}
