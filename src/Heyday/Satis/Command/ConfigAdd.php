<?php

namespace Heyday\Satis\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Composer\Json\JsonFile;

class ConfigAdd extends ConfigInit
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('config-add')
            ->setDescription('Adds configurations to the satis config');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        $repos = $this->getSatisGitRepositories($input, $output);

        $json = new JsonFile($input->getOption('file'));

        $options = $json->read();

        $options['repositories'] = array_merge($options['repositories'], $repos);

        if ($input->isInteractive()) {
            $output->writeln(array(
                '',
                $json->encode($options),
                ''
            ));
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $json->write($options);

        $this->runBuild($input, $output);
    }

}
