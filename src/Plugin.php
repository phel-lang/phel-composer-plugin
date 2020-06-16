<?php

declare(strict_types=1);

namespace Phel\Composer;

use Composer\Composer;
use Composer\Script\Event;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [ScriptEvents::POST_AUTOLOAD_DUMP => 'dumpPhelRuntime'];
    }

    public static function dumpPhelRuntime(Event $composerEvent): void
    {
        $dumper = new DumpRuntime();
        $dumper->run($composerEvent->getComposer());
    }
}
