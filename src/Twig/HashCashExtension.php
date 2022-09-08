<?php
/**
 *
 * Date: 06.09.2022
 * Time: 13:23
 *
 */

namespace Pimcorecasts\Bundle\HashCash\Twig;

use Pimcorecasts\Bundle\HashCashBundle\Service\HashCashService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class HashCashExtension extends AbstractExtension
{

    public function __construct( protected HashCashService $cashService )
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('pc_hash_cash', [$this, 'hashCash'], [ 'is_safe' => ['html'] ]),
        ];
    }

    /**
     * @return HashCashService
     */
    public function hashCash() : HashCashService
    {
        return $this->cashService;
    }
}
