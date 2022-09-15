<?php

declare(strict_types=1);

namespace Pimcorecasts\Bundle\HashCash\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pimcorecasts_hashcash_captcha');
        return $treeBuilder;
    } //: getConfigTreeBuilder
}
