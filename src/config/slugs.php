<?php

return array(
    /*
      |--------------------------------------------------------------------------
      | Create slugs
      |--------------------------------------------------------------------------
      |
      | Defines if the models should be updated with rules for the
      | Sluggable (eloquent-sluggable) package
      |
     */

    'createSlugs' => false,
    
    /*
      |--------------------------------------------------------------------------
      | Select slugs
      |--------------------------------------------------------------------------
      |
      | Defines rules used to pick models for update 
      | Items (Array) - defines the model's field names
      | Function (Enum all|one) - defines the list selection criteria
      |
     */
    
    'selectSlugs' => array(
        'items' => array('slug'),
        'function' => 'one',
        
        
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Configure slugs
      |--------------------------------------------------------------------------
      |
      | Defines the Sluggable default configuration in reference to the fields
      | of a model. The keys of the configureSlugs array can be friendly names 
      | of your configuration rule sets. The order of keys determines the order 
      | in which the rule sets will be matched, first good match is used.
      | 
      | The required and forbidden rules determine if a model matches the 
      | ruleset. If the model has all fields present in required array and none 
      | of the fields in the forbidden array it matches the ruleset and the 
      | associated rules are used with Sluggable.
      |
      | The attribute getter is required if build_from is not a column. In the 
      | example template it just echoes back the field name. The mapGetters 
      | below is also set for example (it is not needed here).
      | 
     */
    
    'configureSlugs' => array(
        'default' => array(
            'required' => array('title','slug'),
            'forbidden' => array(),
            'rules' => array(
                'build_from' => 'title',
                'save_to'    => 'slug',
                'method'     => null,
                'separator'  => '-',
                'unique'     => true,
                'on_update'  => false,
            ),
            'builder' => array(
              'createGetters' => true,
              'mapGetters' => array(
                  array('methodName'=>'Title','fieldName'=>'title'),
                  ),
            ),
        ),
        
    ),
    
);
