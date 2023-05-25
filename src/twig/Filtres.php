<?php

namespace App\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Filtres extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('strip', [$this, 'strip']),
        ];
    }

    public function strip($string): string
    {
        return explode("_", $string)[1];
    }
}
