<?php
declare(strict_types=1);

namespace Bake\Utility\Model;

enum EnumParser
{
    /**
     * @param string|null $casesString
     * @param bool $int
     * @return array<string, int|string>
     */
    public static function parseCases(?string $casesString, bool $int): array
    {
        if ($casesString === null || $casesString === '') {
            return [];
        }

        $enumCases = explode(',', $casesString);

        $definition = [];
        foreach ($enumCases as $k => $enumCase) {
            $case = $value = trim($enumCase);
            if (str_contains($case, ':')) {
                $value = trim(mb_substr($case, strpos($case, ':') + 1));
                $case = mb_substr($case, 0, strpos($case, ':'));
            } elseif ($int) {
                $value = $k;
            }

            $definition[$case] = $int ? (int)$value : $value;
        }

        return $definition;
    }
}
