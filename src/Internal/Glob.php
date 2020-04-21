<?php

namespace Krak\SymfonyMessengerAutoScale\Internal;

/**
 * Implements simple glob matching on strings (used for receiver pattern)
 * Source copied/inspired from: https://github.com/symfony/finder/blob/master/Glob.php
 * Copied file in instead of including all of the finder component to reduce dependencies.
 */
final class Glob
{
    private $glob;
    private $strictLeadingDot;
    private $strictWildcardSlash;
    private $delimiter;
    private $cachedRegex;

    public function __construct(string $glob, bool $strictLeadingDot = true, bool $strictWildcardSlash = true, string $delimiter = '#') {
        $this->glob = $glob;
        $this->strictLeadingDot = $strictLeadingDot;
        $this->strictWildcardSlash = $strictWildcardSlash;
        $this->delimiter = $delimiter;
    }

    public function matches(string $stringToMatch): bool {
        $regex = $this->cachedRegex
            ? $this->cachedRegex
            : $this->cachedRegex = self::patternToRegex($this->glob, $this->strictLeadingDot, $this->strictWildcardSlash, $this->delimiter);

        return preg_match($regex, $stringToMatch) === 1;
    }

    private static function patternToRegex(string $glob, bool $strictLeadingDot = true, bool $strictWildcardSlash = true, string $delimiter = '#'): string {
        $firstByte = true;
        $escaping = false;
        $inCurlies = 0;
        $regex = '';
        $sizeGlob = \strlen($glob);
        for ($i = 0; $i < $sizeGlob; ++$i) {
            $car = $glob[$i];
            if ($firstByte && $strictLeadingDot && '.' !== $car) {
                $regex .= '(?=[^\.])';
            }

            $firstByte = '/' === $car;

            if ($firstByte && $strictWildcardSlash && isset($glob[$i + 2]) && '**' === $glob[$i + 1].$glob[$i + 2] && (!isset($glob[$i + 3]) || '/' === $glob[$i + 3])) {
                $car = '[^/]++/';
                if (!isset($glob[$i + 3])) {
                    $car .= '?';
                }

                if ($strictLeadingDot) {
                    $car = '(?=[^\.])'.$car;
                }

                $car = '/(?:'.$car.')*';
                $i += 2 + isset($glob[$i + 3]);

                if ('/' === $delimiter) {
                    $car = str_replace('/', '\\/', $car);
                }
            }

            if ($delimiter === $car || '.' === $car || '(' === $car || ')' === $car || '|' === $car || '+' === $car || '^' === $car || '$' === $car) {
                $regex .= "\\$car";
            } elseif ('*' === $car) {
                $regex .= $escaping ? '\\*' : ($strictWildcardSlash ? '[^/]*' : '.*');
            } elseif ('?' === $car) {
                $regex .= $escaping ? '\\?' : ($strictWildcardSlash ? '[^/]' : '.');
            } elseif ('{' === $car) {
                $regex .= $escaping ? '\\{' : '(';
                if (!$escaping) {
                    ++$inCurlies;
                }
            } elseif ('}' === $car && $inCurlies) {
                $regex .= $escaping ? '}' : ')';
                if (!$escaping) {
                    --$inCurlies;
                }
            } elseif (',' === $car && $inCurlies) {
                $regex .= $escaping ? ',' : '|';
            } elseif ('\\' === $car) {
                if ($escaping) {
                    $regex .= '\\\\';
                    $escaping = false;
                } else {
                    $escaping = true;
                }

                continue;
            } else {
                $regex .= $car;
            }
            $escaping = false;
        }

        return $delimiter.'^'.$regex.'$'.$delimiter;
    }
}
