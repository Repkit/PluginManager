# PluginManager
a simple observer pattern implementation inspired from zend-eventmanager. For complex workflows use zend-eventmanager

*PluginResponseCollection* is a copy of zend-eventmanager/ResponseCollection (http://github.com/zendframework/zend-eventmanager)

## Usage
- composer require rpk/plugin-manager
- #trigger event in app
  - ```
      $plugin = \RpkPluginManager\PluginChain::getInstance();

      $params = $plugin->prepareArgs(['template'=> 'page::edit', 'data' => $data]);
      
      $plugin->trigger('page::edit-render.pre', $params);
      
      
- #attach to an event
```
return [
   'plugin-manager' => [
        'page::edit-render.pre' => [
            new \RpkPluginManager\Plugin(\Coco\ChangeHome::class),
            new \RpkPluginManager\Plugin(function($params){
                $params['data']['some_new_prop'] = 'some_new_value';
            }, 10, false, \RpkPluginManager\Plugin::TYPE_CALLABLE),
        ]    
    ]
];
```
