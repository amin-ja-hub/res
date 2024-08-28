<?php
namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Service\Service;
use Twig\Environment;
use IntlDateFormatter;
use ReflectionClass;

class TwigExtension extends AbstractExtension
{
    private $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('localizeddatetr', [$this, 'persiandatetimeFilter'], ['needs_environment' => true]),
        ];
    }

    public function getFunctions()
    {
        // Predefined array of Twig functions to expose
        return [
            new \Twig\TwigFunction('findEntitiesWithCriteria', [$this->service, 'findEntitiesWithCriteria']),
        ];
    }

    public function persiandatetimeFilter(Environment $env, $date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $timezone = null, $format = null)
    {
        $date = twig_date_converter($env, $date, $timezone);

        $formatValues = [
            'none'   => IntlDateFormatter::NONE,
            'short'  => IntlDateFormatter::SHORT,
            'medium' => IntlDateFormatter::MEDIUM,
            'long'   => IntlDateFormatter::LONG,
            'full'   => IntlDateFormatter::FULL,
            'custom' => 'd MMMM yyyy'  // Custom format for Persian date
        ];

        // If a specific format is provided, use it; otherwise, use the one from the formatValues array
        $format = $format ?? $formatValues[$dateFormat];

        $formatter = IntlDateFormatter::create(
            $locale ?? 'fa_IR@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            $date->getTimezone()->getName(),
            IntlDateFormatter::TRADITIONAL,
            $format
        );

        return $formatter->format($date->getTimestamp());
    }

}
