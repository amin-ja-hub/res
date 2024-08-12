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
    private $persianDateConverter;

    public function __construct(Service $persianDateConverter)
    {
        $this->persianDateConverter = $persianDateConverter;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('localizeddatetr', [$this, 'persiandatetimeFilter'], ['needs_environment' => true]),
        ];
    }

    public function getFunctions()
    {
        $functions = [];

        // Get all public methods from the Service class
        $reflection = new ReflectionClass($this->persianDateConverter);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $functions[] = new TwigFunction($method->getName(), [$this->persianDateConverter, $method->getName()]);
        }

        return $functions;
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
        ];

        $formatter = IntlDateFormatter::create(
            $locale,
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            $date->getTimezone()->getName(),
            IntlDateFormatter::TRADITIONAL,
            $format
        );

        return $formatter->format($date->getTimestamp());
    }
}
