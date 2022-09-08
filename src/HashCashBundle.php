<?php

namespace Pimcorecasts\Bundle\HashCash;


use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class HashCashBundle extends AbstractPimcoreBundle
{
    public function getVersion()
    {
        try{
            $version = \Composer\InstalledVersions::getVersion('pimcorecasts/hashcash-form-bundle');
        }catch( \Exception $e ){
            $version = \PackageVersions\Versions::getVersion( 'pimcorecasts/hashcash-form-bundle' );
        }

        return $version;
    }

}
