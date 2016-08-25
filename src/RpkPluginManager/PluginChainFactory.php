<?php

namespace RpkPluginManager;

use Interop\Container\ContainerInterface;

class PluginChainFactory
{
    public function __invoke(ContainerInterface $Container)
    {
        $plugin = PluginChain::getInstance();
        $plugin->setContainer($Container);
        
        return $plugin;
    }
}