<?php

declare(strict_types=1);

namespace Phel\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\Event;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

final class Plugin implements PluginInterface, EventSubscriberInterface
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
        $io = $composerEvent->getIO();
        $io->write('<info>phel/phel-composer-plugin:</info> Generating Phel runtime');
        $dumper = new DumpRuntime();
        $dumper->run($composerEvent->getComposer());
    }
}
