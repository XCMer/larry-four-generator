<?php

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
      | Namespaces
      |--------------------------------------------------------------------------
      |
      | Defines which namespaces are added into the model by default
      |
      |
     */

    'namespaces' => array(
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Parent class
      |--------------------------------------------------------------------------
      |
      | Defines the parent class that the updated model extends
      |
      |
     */

    'parentClass' => '',
    
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
    
    /*
      |--------------------------------------------------------------------------
      | Fillable fields
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with fillable array stub with
      | default content. Fillable fields can be mass assigned.
      |
     */

    'fillModels' => array(
        'createFillable' => false,
        'defaultFillable' => array(),
        'allFillable' => false,
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Guraded fields
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with guraded array stub with
      | default content. Guarded fields cannot be mass assigned.
      |
     */

    'guardModels' => array(
        'createGuarded' => false,
        'defaultGuarded' => array(),
        'allGuarded' => false,
    ),
    
    /*
      |--------------------------------------------------------------------------
      | Hidden fields
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with hidden array stub with
      | defualt content. Hidden fields are excluded from model's JSON form.
      |
     */

    'hideModels' => array(
        'createHidden' => false,
        'defaultHidden' => array(),
        'allHidden' => false,
    ),
    
    
    
    /*
      |--------------------------------------------------------------------------
      | Ardent configuration
      |--------------------------------------------------------------------------
      |
      | Defines Ardent (laravelbook/ardent) specific configuration:
      | $autoHydrateEntityFromInput = true;
      | $autoPurgeRedundantAttributes = true;
      | $passwordAttributes  = array('password');
      | $autoHashPasswordAttributes = true;
      |
     */

    'ardentConfig' => array(
        'autoHydrate' => false,
        'autoPurge' => false,
        'hashFields' => array(),
        'autoHash' => false,
    ),
    
     /*
      |--------------------------------------------------------------------------
      | Callback configuration
      |--------------------------------------------------------------------------
      |
      | Defines whether to place event hook stubs in the model file
      |
      |
     */

    'exposeHooks' => array(
        'beforeSave' => false,
        'beforeUpdate' => false,
        'beforeDelete' => false,
        'beforeValidate' => false,
        'afterSave' => false,
        'afterUpdate' => false,
        'afterDelete' => false,
        'afterValidate' => false,
    ),

    
    /*
      |--------------------------------------------------------------------------
      | Defaults
      |--------------------------------------------------------------------------
      |
      | Defines default model parameters
      |
      |
     */

    'defaults' => array(
        'parentClass' => 'Eloquent'
    ),
);
