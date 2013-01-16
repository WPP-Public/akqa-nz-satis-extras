<?php

namespace Heyday\Satis\Command;

use Composer\Satis\Command\BuildCommand as SatisBuildCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Composer\Json\JsonFile;
use Composer\Config;

class BuildCommand extends SatisBuildCommand
{
    protected function configure()
    {
        parent::configure();
        $this->addOption(
            'doc-root',
            null,
            InputOption::VALUE_OPTIONAL,
            'Document root (if different from output-dir)',
            null
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose');
        $file = new JsonFile($input->getArgument('file'));
        if (!$file->exists()) {
            $output->writeln('<error>File not found: '.$input->getArgument('file').'</error>');

            return 1;
        }
        $config = $file->read();

        // disable packagist by default
        unset(Config::$defaultRepositories['packagist']);

        $composer = $this->getApplication()->getComposer(true, $config);
        $composer->getConfig()->merge(array(
            'config' => array(
                'homepage'      => $config['homepage'],
                'output-dir'    => $input->getArgument('output-dir'),
                'cache-web-dir' => $input->getArgument('output-dir') . '/zips',
                'doc-root'      => $input->getOption('doc-root')
            )
        ));

        parent::execute($input, $output);
    }
}
