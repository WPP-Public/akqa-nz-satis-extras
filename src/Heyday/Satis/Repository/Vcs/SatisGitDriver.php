<?php

namespace Heyday\Satis\Repository\Vcs;

use Composer\Repository\Vcs\GitDriver;
use Composer\Json\JsonFile;
use Composer\Util\Filesystem;

class SatisGitDriver extends GitDriver
{

    public function initialize()
    {
        $fs = new Filesystem();
        $fs->ensureDirectoryExists($this->config->get('cache-web-dir'));
        if (!is_writable($this->config->get('cache-web-dir'))) {
            throw new \RuntimeException('Can not make archives of '.$this->url.'. The "'.$this->config->get('cache-web-dir').'" directory is not writable by the current user.');
        }

        $this->webCacheDir = realpath($this->config->get('cache-web-dir')) . '/' . preg_replace('{[^a-z0-9.]}i', '-', $this->url) . '/';
        $fs->ensureDirectoryExists($this->webCacheDir);

        if (!is_writable($this->webCacheDir)) {
            throw new \RuntimeException('Can not make archives of '.$this->url.'. The "'.$this->webCacheDir.'" directory is not writable by the current user.');
        }
        parent::initialize();
    }

    public function getDist($identifier)
    {
        $tag = array_search($identifier, $this->getTags());
        if ($tag) {
            $zip = $this->webCacheDir . $identifier . '.zip';
            //If the zip doesn't exist
            if (!file_exists($zip)) {
                $result = null;
                $this->process->execute("git archive --format=zip --output=\"$zip\" $identifier", $result, $this->repoDir);
                if ($this->getGitComposerInformation($identifier)) {
                    $composerFile = new JsonFile($this->webCacheDir . 'composer.json');
                    $composerFile->write($this->getComposerInformation($identifier));
                    if (0 !== $this->process->execute("zip -g $zip composer.json", $result, $this->webCacheDir)) {
                        return null;
                    }

                    if (isset($this->repoConfig['composer-config']) && $this->getDynamicComposerInformationByIdentifier($this->repoConfig['composer-config'], $identifier)) {
                        if (0 !== $this->process->execute("zip -d $zip composer.lock", $result, $this->webCacheDir)) {
                            return null;
                        }
                    }
                }
            }

            return array(
                'type' => 'zip',
                'url' => rtrim($this->config->get('homepage'), '/') . str_replace(realpath($this->config->get('doc-root') ?: $this->config->get('output-dir')), '', $zip),
                'reference' => $tag
            );
        } else {
            return null;
        }
    }

    protected function getDynamicComposerInformationByIdentifier($composerInformation, $identifier)
    {
        if (isset($composerInformation[$identifier])) {
            return $composerInformation[$identifier];
        } elseif (isset($composerInformation['*'])) {
            return $composerInformation['*'];
        }
        return false;
    }

    public function getComposerInformation($identifier)
    {
        if (!isset($this->infoCache[$identifier])) {
            $composer = $this->getGitComposerInformation($identifier);
            if (!$composer) {
                if (isset($this->repoConfig['composer-config'])) {
                    $composer = $this->getDynamicComposerInformationByIdentifier($this->repoConfig['composer-config'], $identifier);
                }
                if (!$composer) {
                    return;
                }
            } else {
                $composer = JsonFile::parseJson($composer, sprintf('%s:composer.json', escapeshellarg($identifier)));
                if (!isset($composer['time'])) {
                    $this->process->execute(sprintf('git log -1 --format=%%at %s', escapeshellarg($identifier)), $output, $this->repoDir);
                    $date = new \DateTime('@'.trim($output), new \DateTimeZone('UTC'));
                    $composer['time'] = $date->format('Y-m-d H:i:s');
                }

                if (isset($this->repoConfig['composer-config'])) {
                    if ($overwrite = $this->getDynamicComposerInformationByIdentifier($this->repoConfig['composer-config'], $identifier)) {
                        $composer = array_merge($composer, $overwrite);
                    }
                }

            }
            $this->infoCache[$identifier] = $composer;
        }

        return $this->infoCache[$identifier];
    }

    protected function getGitComposerInformation($identifier)
    {
        $this->process->execute(sprintf('git show %s', sprintf('%s:composer.json', escapeshellarg($identifier))), $composer, $this->repoDir);

        if (!trim($composer)) {
            return false;
        } else {
            return $composer;
        }
    }
}
