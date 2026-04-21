<?php

namespace App\Support;

class ThemePalette
{
    public const DEFAULT_PRIMARY = '#D97706';

    public static function normalize(?string $hex): string
    {
        $hex = strtoupper(trim((string) $hex));

        if (preg_match('/^#[0-9A-F]{6}$/', $hex) !== 1) {
            return self::DEFAULT_PRIMARY;
        }

        return $hex;
    }

    public static function cssVariables(?string $hex): array
    {
        $primary = self::hexToRgb(self::normalize($hex));

        return [
            '--color-primary-rgb' => self::toRgbString($primary),
            '--color-primary-dim-rgb' => self::toRgbString(self::mix($primary, [0, 0, 0], 0.18)),
            '--color-primary-container-rgb' => self::toRgbString(self::mix($primary, [255, 255, 255], 0.82)),
            '--color-primary-fixed-rgb' => self::toRgbString(self::mix($primary, [255, 255, 255], 0.82)),
            '--color-primary-fixed-dim-rgb' => self::toRgbString(self::mix($primary, [255, 255, 255], 0.68)),
            '--color-on-primary-rgb' => self::toRgbString(self::contrastColor($primary)),
            '--color-on-primary-container-rgb' => self::toRgbString(self::mix($primary, [0, 0, 0], 0.6)),
            '--color-on-primary-fixed-rgb' => self::toRgbString(self::mix($primary, [0, 0, 0], 0.72)),
            '--color-on-primary-fixed-variant-rgb' => self::toRgbString(self::mix($primary, [0, 0, 0], 0.24)),
            '--color-inverse-primary-rgb' => self::toRgbString(self::mix($primary, [255, 255, 255], 0.45)),
            '--color-surface-tint-rgb' => self::toRgbString($primary),
            '--color-primary-hex' => self::normalize($hex),
        ];
    }

    public static function inlineCssVariables(?string $hex): string
    {
        return collect(self::cssVariables($hex))
            ->map(fn ($value, $key) => "{$key}: {$value}")
            ->implode('; ');
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private static function hexToRgb(string $hex): array
    {
        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }

    /**
     * @param array{0:int,1:int,2:int} $base
     * @param array{0:int,1:int,2:int} $target
     * @return array{0:int,1:int,2:int}
     */
    private static function mix(array $base, array $target, float $amount): array
    {
        return [
            (int) round(($base[0] * (1 - $amount)) + ($target[0] * $amount)),
            (int) round(($base[1] * (1 - $amount)) + ($target[1] * $amount)),
            (int) round(($base[2] * (1 - $amount)) + ($target[2] * $amount)),
        ];
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     * @return array{0:int,1:int,2:int}
     */
    private static function contrastColor(array $rgb): array
    {
        $luminance = ((0.299 * $rgb[0]) + (0.587 * $rgb[1]) + (0.114 * $rgb[2])) / 255;

        return $luminance > 0.62 ? [43, 52, 55] : [255, 255, 255];
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function toRgbString(array $rgb): string
    {
        return implode(' ', $rgb);
    }
}
