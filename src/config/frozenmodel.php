<?php
//TODO
return array(
    /*
      |--------------------------------------------------------------------------
      | Process models
      |--------------------------------------------------------------------------
      |
      | Defines if the models should be updated with additional namespaces,
      | parent class and validation rules array stub
      |
     */

    'processModels' => false,
    
    /*
      |--------------------------------------------------------------------------
      | Update models selection
      |--------------------------------------------------------------------------
      |
      | Defines rules used to pick models for update 
      | Items (Array) - defines the model names
      | Function (Enum all|only|except) - defines the list selection criteria
      |
     */
    
    'processSelection' => array(
        'items' => array(),
        'function' => 'except',
        
    ),
    
    
   
    
    /*
      |--------------------------------------------------------------------------
      | Validate models
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with validation rules array stub
      |
      |
     */

    'validateModels' => array(
        'createRules' => false,
        'includeEmpty' => true,
        'defaultRules' => array(),
        'mapRules' => array(
            'fieldTypes' => array(
            'string'=>array(), 
            'integer'=>array('integer'), 
            'bigInteger'=>array('integer'), 
            'smallInteger'=>array('integer'),
            'float'=>array('numeric'),
            'decimal'=>array('numeric'),
            'boolean'=>array(),
            'date'=>array(),
            'dateTime'=>array(),
            'time'=>array(),
            'timestamp'=>array(),
            'text'=>array(),
            'enum'=>array()
                ),
            'fieldNames' => array(
                
            ),
        ),
    ),
    
    
);
