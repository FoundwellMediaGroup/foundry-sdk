<?php

declare(strict_types=1);

namespace Foundwell;

final class Version
{
    private const FALLBACK_VERSION = '0.3.2-alpha';

    private static ?string $current = null;

    public static function current(): string
    {
        if (self::$current !== null) {
            return self::$current;
        }

        $versionFile = dirname(__DIR__) . '/VERSION';
        if (is_file($versionFile)) {
            $version = trim((string) file_get_contents($versionFile));
            if ($version !== '') {
                return self::$current = $version;
            }
        }

        return self::$current = self::FALLBACK_VERSION;
    }

    private function __construct()
    {
    }
}
