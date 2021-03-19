<?php

declare(strict_types=1);

namespace Phel\Composer;

use Composer\Composer;

final class DumpRuntime
{
    public function run(Composer $composer): void
    {
        $packages = [
            $composer->getPackage(),
            ...$composer->getLocker()->getLockedRepository()->getPackages()
        ];

        /** @var array<string, array<string>> $loadConfig */
        $loadConfig = [];
        foreach ($packages as $i => $package) {
            $extra = $package->getExtra();
            if (!isset($extra["phel"])) {
                continue;
            }

            // First package is the current project (no dependency)
            $isRootPackage = $i === 0;
            $pathPrefix = $isRootPackage ? '/..' : '/' . $package->getName();
            $loaderNames = $isRootPackage ? ["loader", "loader-dev"] : ["loader"];

            /** @var array */
            $phelConfig = $extra["phel"];
            foreach ($loaderNames as $loaderName) {
                if (!isset($phelConfig[$loaderName])) {
                    continue;
                }

                /** @var array<string, string|array<string>> $packageLoadConfig */
                $packageLoadConfig = $phelConfig[$loaderName];
                foreach ($packageLoadConfig as $ns => $pathList) {
                    if (!isset($loadConfig[$ns])) {
                        $loadConfig[$ns] = [];
                    }

                    if (is_string($pathList)) {
                        $pathList = [$pathList];
                    }

                    foreach ($pathList as $path) {
                        $loadConfig[$ns][] = $pathPrefix . '/' . $path;
                    }
                }
            }
        }


        /** @var string $vendorDir */
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $template = $this->createRuntimeScript($loadConfig);
        file_put_contents($vendorDir . '/PhelRuntime.php', $template);
    }

    /**
     * @param array<string, array<string>> $loadConfig
     */
    private function createRuntimeScript(array $loadConfig): string
    {
        $template = <<<'EOF'
<?php

// @generated by Phel
use Phel\Runtime\RuntimeSingleton;

require __DIR__ .'/autoload.php';

$rt = RuntimeSingleton::initialize();

EOF;

        foreach ($loadConfig as $ns => $paths) {
            $encodedNs = addslashes($ns);
            $pathString = implode("', __DIR__ . '", array_map('addslashes', $paths));
            $template .= "\$rt->addPath(\"$encodedNs\", [__DIR__ . '$pathString']);\n";
        }

        $template .= "\$rt->loadNs(\"phel\\\\core\");\n";
        $template .= "return \$rt;\n";

        return $template;
    }
}
