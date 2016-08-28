<?php

namespace RpkPluginManager;

use Interop\Container\ContainerInterface;

class PluginChain
{
    /**
     * @var PluginChain
     */
    protected static $instance;
    
    protected static $events = [];
    
    protected static $loaded = false;
    
    protected $container = null;
    
    /**
     * Singleton
     */
    protected function __construct()
    {
        // do nothing
        return;
    }
    
    
    /**
     * Singleton
     *
     * @return void
     */
    private function __clone()
    {
        // do nothing
        return;
    }
    
    /**
     * Retrieve instance
     *
     * @return PluginChain
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::setInstance(new static());
        }
        return static::$instance;
    }
    
    /**
     * Set the singleton to a specific PluginChain instance
     *
     * @param PluginChain $instance
     * @return void
     */
    public static function setInstance(PluginChain $instance)
    {
        static::$instance = $instance;
    }
    
    /**
     * Is a singleton instance defined?
     *
     * @return bool
     */
    public static function hasInstance()
    {
        return (static::$instance instanceof PluginChain);
    }
    
    /**
     * Reset the singleton instance
     *
     * @return void
     */
    public static function resetInstance()
    {
        static::$instance = null;
    }
    
    /**
     * Trigger listeners
     *
     * @param  string $EventKey - the event key used by plugins to attach
     * @param  array $Params - params that will be passed to the plugin
     * @param  null|callable $Callback - callback that will be runned after response is added to the collection
     *          may act like a validator - if return true then it will trigger stop propagation on collection
     * @return ResponseCollection
     */
    public function trigger($EventKey, $Params = [], callable $Callback = null)
    {
        // TODO [IMPROVEMENT]: analyse if make sens to create plugin only for cli
        if(!static::$loaded){
           $this->load();
        }
        
        $listeners = array_merge_recursive(
            isset(static::$events[$EventKey]) ? static::$events[$EventKey] : [],
            isset(static::$events['*']) ? static::$events['*'] : []
        );
        
        krsort($listeners, SORT_NUMERIC);

        // var_dump($listeners);exit(__METHOD__.'::'.__LINE__);        
        $responses = new PluginResponseCollection();
        
        foreach ($listeners as $priority => $listenersByPriority) {
           
            foreach ($listenersByPriority as $type => $listener) {
                
                if($listener->Type == Plugin::TYPE_CALLABLE){
                    
                    $plugin = $listener->Plugin;
                    
                }else{
                    /*we sacrifice this for performance*/
                    //if($this->getContainer()->has($listener->name())){
                        
                        $plugin = $this->getContainer()->get($listener->Plugin);
                        
                    // }
                    
                }
                
                $response = $plugin($Params, $EventKey);
                $responses->push($response);
                
                // if listener request stop propagation then exit loops
                if($listener->StopPropagation){
                    $responses->setStopped(true);
                    break 2;
                }
                
                // if callback return true - stop propagation
                if ($Callback && ($Callback($response) === TRUE)) {
                    $responses->setStopped(true);
                    break 2;
                }
                
            }
            
       }
       
       return $responses;
       
    }
    
    /**
     * Attach listener
     *
     * @param  string $EventKey - the event key used by plugins to attach
     * @param  PluginInterface $Listener - plugin attached to the event
     * @param  int $Priority - priority of the plugin in execution queue
     * @return PluginInterface
     */
    public function attach($EventKey, $Listener, $Priority = 1)
    {
        // static::$events[$EventKey][((int) $Priority)][] = $Listener;
        static::$events[$EventKey][$Priority][] = $Listener;

        return $Listener;
    }
    
    /**
     * @inheritDoc
     */
    public function clearListeners($EventKey)
    {
        if (isset(static::$events[$EventKey])) {
            unset(static::$events[$EventKey]);
        }
    }
    
    /**
     * Prepare arguments
     *
     * Use this method if you want to be able to modify arguments from within a
     * listener. It returns an ArrayObject of the arguments, which may then be
     * passed to trigger().
     *
     * @param  array $Args
     * @return ArrayObject
     */
    public function prepareArgs(array $Args)
    {
        return new \ArrayObject($Args);
    }
    
    /**
     * Get IoC
     * 
     * @return null|ContainerInterface
     */ 
    public function getContainer()
    {
        $container = $this->container;
        if(!$container){
            $container = require 'config/container.php';
        }
        return $container;
    }
    
    /**
     * Set IoC
     * 
     * @param ContainerInterface $Container
     */ 
    public function setContainer(ContainerInterface $Container)
    {
        $this->container = $Container;
    }
    
    /**
     * load events with their plugins
     */ 
    public function load()
    {
        static::$loaded = true;
        
        $container = $this->getContainer();
        
        if($container){
            $config = $container->get('config');
            $events = $config['plugin-manager'];
            foreach($events as $event => $plugins){
                foreach($plugins as $type => $plugin){
                    $this->attach($event, $plugin, $plugin->Priority);
                }
            }
        }
        
        return true;
    }
    
}