<?php

namespace Heyday\Satis\Repository;

use Composer\Repository\VcsRepository;
use Composer\IO\IOInterface;
use Composer\Config;

class SatisGitRepository extends VcsRepository
{
    public function __construct(array $repoConfig, IOInterface $io, Config $config, array $drivers = null)
    {
        parent::__construct($repoConfig, $io, $config, $drivers);
        $this->drivers['satis-git'] = 'Heyday\Satis\Repository\Vcs\SatisGitDriver';
    }
}
