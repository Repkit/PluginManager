<?php

namespace RpkPluginManager;

class Plugin
{
    const TYPE_CALLABLE = 'callable';
    
    public $Plugin;
    public $Priority;
    public $StopPropagation;
    public $Type;
    
    public function __construct($Plugin, $Priority = 1, $StopPropagation = false, $Type = 'factory')
    {
        $this->Plugin = $Plugin;
        $this->Priority = $Priority;
        $this->StopPropagation = $StopPropagation;
        $this->Type = $Type;
    }
    
}