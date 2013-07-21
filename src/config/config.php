<?php

return array(
    /*
      |--------------------------------------------------------------------------
      | Update models
      |--------------------------------------------------------------------------
      |
      | Defines if the models should be updated with additional namespaces,
      | parent class and validation rules array stub
      |
     */

    'updateModels' => false,
    
    /*
      |--------------------------------------------------------------------------
      | Ommit models
      |--------------------------------------------------------------------------
      |
      | Defines which models should be excluded from the update procedure
      |
      |
     */

    'ommitModels' => array(
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
      | Validation rules
      |--------------------------------------------------------------------------
      |
      | Defines if the model should be updated with validation rules array stub
      |
      |
     */

    'validateModels' => false,
    
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
