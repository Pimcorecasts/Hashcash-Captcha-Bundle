<?php

namespace Pimcorecasts\Bundle\HashCash;


use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class HashCashBundle extends AbstractPimcoreBundle
{
    public function getVersion(): string
    {
        try{
            $version = \Composer\InstalledVersions::getVersion('pimcorecasts/hash-cash-captcha-bundle');
        }catch( \Exception $e ){
            $version = \PackageVersions\Versions::getVersion( 'pimcorecasts/hash-cash-captcha-bundle' );
        }

        return $version;
    }

}
