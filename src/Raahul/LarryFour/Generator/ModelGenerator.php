<?php namespace Raahul\LarryFour\Generator;

use Config;
class ModelGenerator
{
    /**
     * Stores the model template for use throughout the lifetime of this instance,
     * which saves is from reading the template again and again from a file
     * @var string
     */
    private $modelTemplate;

    /** 
     * Stores the relational function template for use throughout the lifetime of this instance,
     * which saves is from reading the template again and again from a file
     * @var string
     */
    private $relationalFunctionTemplate;

     
    /**
     * Stores the hook template for use throughout the lifetime of this instance,
     * which saves is from reading the template again and again from a file
     * @var string
     */
    private $hookTemplate;

    /**
     * Stores the attribute getter template for use throughout the lifetime of 
     * this instance, which saves is from reading the template again and again
     * from a file
     * @var string
     */
    private $getterTemplate;


    /**
     * Load the model template
     */
    public function __construct()
    {
        // Load the model template
        $this->modelTemplate = file_get_contents(__DIR__ . '/templates/model');

        // Load the hook template
        $this->hookTemplate = file_get_contents(__DIR__ . '/templates/modelhook');

        // Load the getter template
        $this->getterTemplate = file_get_contents(__DIR__ . '/templates/modelgetter');

        // Load the relation function block template
        $this->relationalFunctionTemplate =
            file_get_contents(__DIR__ . '/templates/relational_function');
    }


    /**
     * Generate the model file contents from the templates and the model
     * object provided
     * @param  \Raahul\LarryFour\Model $model   The model object whose model file has to be generated
     * @param  array $migrations                An array of migration objects
     * @return string                           The model file contents
     */
    public function generate($model, $migrations)
    {
        // Store the local version of the template
        $result = $this->modelTemplate;
        
        // Get model columns
        $migration = $migrations[$model->modelName];
        $columns = $migration->all();
        
         // Initialize parent class variable
        $parentClass = 'Eloquent';
        
        // Initialize processing config
        $processModelFlag = false;
        $processModelItems = Config::get('larryfour::validation.processSelection.items',array());
        $processModelFunction = Config::get('larryfour::validation.processSelection.function');
            
        if(Config::get('larryfour::validation.processModels',false))
        {
            switch($processModelFunction)
            {
            case "all":
                $processModelFlag = true;
                break;
            
            case "only":
                $processModelFlag = in_array($model->modelName, $processModelItems);
                break;
            
            case "except":
                $processModelFlag = !in_array($model->modelName, $processModelItems);
                break;
            
            default:
                break;
            }
        
        }
        
        // Model extends updated parent class or add default
        $parentClass = $processModelFlag ? Config::get('larryfour::validation.parentClass') : Config::get('larryfour::validation.defaults.parentClass');

        // Add in the model namespaces or add blank
        $result = $this->addNamespaces($result, $processModelFlag ? Config::get('larryfour::validation.namespaces') : array());

        // Add in the validation rules
        $result = $this->addValidationRulesIfNeeded($result, $columns, $processModelFlag ? Config::get('larryfour::validation.validateModels') : false);

        // Add in the Ardent specific config
        $result = $this->addArdentConfigIfNeeded($result, $processModelFlag ? Config::get('larryfour::validation.ardentConfig') : array());

        // Add in the Eloquent specific config
        $result = $this->addFillableIfNeeded($result, $processModelFlag ? Config::get('larryfour::validation.fillModels') : false);
        $result = $this->addGuardedIfNeeded($result, $processModelFlag ? Config::get('larryfour::validation.guardModels') : false);
        $result = $this->addHiddenIfNeeded($result, $processModelFlag ? Config::get('larryfour::validation.hideModels') : false);
        $result = $this->addVisableIfNeeded($result, $processModelFlag ? Config::get('larryfour::validation.showModels') : false);

        // Expose the event hook stubs
        $result = $this->addHooksIfNeeded($result, $processModelFlag ? Config::get('larryfour::validation.exposeHooks') : array());
        
        // Initialize processing config
        $selectSlugFlag = false;
        $selectSlugItems = Config::get('larryfour::slugs.selectSlugs.items');
        $selectSlugFunction = Config::get('larryfour::slugs.selectSlugs.function');
        $modelFields = array_keys($columns);

        $configureSlugFlag = false;
        $configureSlugItems = Config::get('larryfour::slugs.configureSlugs');
        $configureSlugRuleset = array();
        $configureSlugGetters = array();
                                
        
        if(Config::get('larryfour::slugs.createSlugs',false))
        {
            switch($selectSlugFunction)
            {
            case "all":
                $selectSlugFlag = ( count(array_intersect($selectSlugItems, $modelFields)) >= count($selectSlugItems) ) ? true : false;
                break;
            
            case "one":
                $selectSlugFlag = ( count(array_intersect($selectSlugItems, $modelFields)) > 0 ) ? true : false;
                break;
            
            default:
                break;
            }
            
            if($selectSlugFlag)
            {
                
                foreach($configureSlugItems as $key=>$slugs)
                {
                    if( count(array_intersect($slugs['required'], $modelFields)) > 0 && count(array_intersect($slugs['forbidden'], $modelFields)) == 0)
                    {
                        $configureSlugFlag = true;
                        $configureSlugRuleset = $slugs['rules'];
                        $configureSlugGetters = $slugs['builder'];
                        break;
                    }
                }
                
                if($configureSlugFlag == false && isset($configureSlugItems['default']))
                {
                        $slugs = $configureSlugItems['default']; 
                        $configureSlugFlag = true;
                        $configureSlugRuleset = $slugs['rules'];
                        $configureSlugGetters = $slugs['builder'];
                }
                
                
            }
            
        }
        $result = $this->addSlugRulesIfNeeded($result, $configureSlugFlag ? $configureSlugRuleset : array());
        $result = $this->addSlugGettersIfNeeded($result, $configureSlugFlag ? $configureSlugGetters : array());
        
        
        // Add in the model parent class
        $result = $this->addParentClass($result, $parentClass);
        
        
        //TODO
        //add internal uses implementation
        //add factory muff config and implementation
        //implement model-parentclass and model-namespaces from array
        
        //$result = $this->addInternalUseIfNeeded($result,  Config::get('larryfour::validation.internalUse.'.$model->modelName),false);
        // Add in the model parent class
        //$result = $this->addInternalUseIfNeeded($result,  Config::get('larryfour::validation.internalUse.'.$model->modelName),false);
        
        
        // Add in the model name
        $result = $this->addModelName($result, $model->modelName);

        // Add in the timestamps
        $result = $this->addTimestampsIfNeeded($result, $model->timestamps);

        // Add in the softDeletes
        $result = $this->addSoftDeletesIfNeeded($result, $model->softDeletes);

        // Add in the primary key if needed
        $result = $this->addPrimaryKeyIfNeeded($result, $model->primaryKey);

        // Add in the table name if needed
        $result = $this->addTableNameIfNeeded($result, $model->tableName);

        // Add in all the functions
        foreach ($model->all() as $functionName => $functionData)
        {
            $functionBlock = $this->getRelationFunction($functionName, $functionData);
            $result = $this->addRelationFunction($result, $functionBlock);
        }

        // Remove the extraneous relational function tag
        $result = $this->removeRelationFunctionTag($result);

        // Return the result
        return $result;
    }
     
     /**
     * Given the model file contents, put in the used namespaces in proper
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array $namespaces         Array containing used namespaces
     * @return string                    The updated model file contents
     */
    private function addNamespaces($modelFileContents, $namespaces)
    {
        $namespaces = array_map(function($item){ return "use ".$item."; "; }, $namespaces);
        return str_replace('{{namespaces}}', implode("\n",$namespaces), $modelFileContents);
    }
    
    /**
     * Given the model file contents, put in the model parent class in the 
     * appropriate location
     * @param  string $modelFileContents The contents of the model file
     * @param  string $parentClass       The name of the parent class
     * @return string                    The updated model file contents
     */
    private function addParentClass($modelFileContents, $parentClass)
    {
        return str_replace('{{parentClass}}', $parentClass, $modelFileContents);
    }

     /**
     * Given the model file contents, put in the validation rules array stub in
     * appropriate location
     * @param  string $modelFileContents The contents of the model file
     * @param  array $columns            Array of the model columns
     * @param  boolean $validationConfig  Insert the array stub
     * @return string                    The updated model file contents
     */
    private function addValidationRulesIfNeeded($modelFileContents, $columns, $validationConfig)
    {
         $rules = '';
         
        if (!is_array($validationConfig) || empty($validationConfig))
        {
            
        }
        elseif($validationConfig['createRules'])
        {
            $rules = 'public static $rules = array('."\n";
            $validationRules = $validationConfig['mapRules'];
            
            foreach($columns as $fieldName=>$fieldDetails)
            {
                $currentRules = array();
                
                if(key_exists($fieldName, $validationRules['fieldNames']))
                {
                    $currentRules = array_merge($currentRules, $validationRules['fieldNames'][$fieldName]);
                }
                
                if(key_exists($fieldDetails['type'], $validationRules['fieldTypes']))
                {
                    $currentRules = array_merge($currentRules, $validationRules['fieldTypes'][$fieldDetails['type']]);
                }
                
                if($fieldDetails['type'] == 'enum')
                {
                    array_push($currentRules, "in:".implode(',',$fieldDetails['parameters']));
                }
                
                if(!isset($fieldDetails['nullable']))
                {
                    array_push($currentRules, "required");
                }
                elseif(!($fieldDetails['nullable']))
                {
                    array_push($currentRules, "required");
                }
                                                
                if(!empty($fieldDetails['parameters']))
                {
                    $param = current($fieldDetails['parameters']);
                    if((int)$param > 1)
                    {
                        array_push($currentRules, "max:".$param);
                    }
                }

                if(!empty($currentRules))
                {
                     $rules .= "        '".$fieldName."' => array"."(".implode(',',array_map(function($item){ return "'".$item."'"; }, $currentRules))."), "."\n";
                }
                elseif($validationConfig['includeEmpty'])
                {
                     $rules .= "        '".$fieldName."' => array"."(), "."\n";
                }
                               
            }
       
            $rules .= '    ); '."\n";
        }
                
        return str_replace('{{validationRules}}',
            $rules,
            $modelFileContents
        );
        
    }
    
    
    /**
     * Given the model file contents, put in the Sluggable rules in appropriate 
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array  $slugRulesConfig      slugRules config array
     * @return string                    The updated model file contents
     */
    private function addSlugRulesIfNeeded($modelFileContents, $slugRulesConfig)
    {
        $configs = '';
        if (!is_array($slugRulesConfig) || empty($slugRulesConfig))
        {
            
        }
        else
        {
                $configs .= 'public static $sluggable = array'."(".implode(',',array_map(function($key, $item){ return "'".$key."' => '".$item."'"; }, array_keys($slugRulesConfig),$slugRulesConfig))."); "."\n";
        }
        
        
        return str_replace('{{sluggableRules}}',
            $configs,
            $modelFileContents
        );
    }
    
    /**
     * Given the model file contents, put in the Sluggable getters in appropriate 
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array  $slugGettersConfig      slugGetters config array
     * @return string                    The updated model file contents
     */
    private function addSlugGettersIfNeeded($modelFileContents, $slugGettersConfig)
    {
        $configs = '';
        if (!is_array($slugGettersConfig) || empty($slugGettersConfig))
        {
            
        }
        elseif($slugGettersConfig['createGetters'])
        {
            $template = $this->getterTemplate;

            foreach($slugGettersConfig['mapGetters'] as $key=>$getter)
            {
                $configs .= str_replace('{{fieldName}}', $getter['fieldName'], str_replace('{{methodName}}', $getter['methodName'], $template)) . " \n";
            }
            
        }
                
        return str_replace('{{sluggableGetters}}',
            $configs,
            $modelFileContents
        );
    }
    
    
    
    
    /**
     * Given the model file contents, put in the fillable config in appropriate 
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array  $fillableConfig      fillable config array
     * @return string                    The updated model file contents
     */
    private function addFillableIfNeeded($modelFileContents, $fillableConfig)
    {
        $configs = '';
        if (!is_array($fillableConfig) || empty($fillableConfig))
        {
            
        }
        elseif($fillableConfig['createFillable'])
        {
            if($fillableConfig['allFillable'] == true)
            {
                $configs .= 'protected $fillable = array'."('*'); "."\n";
            }
            else
            {
                $configs .= 'protected $fillable = array'."(".implode(',',array_map(function($item){ return "'".$item."'"; }, $fillableConfig['defaultFillable']))."); "."\n";
            }
            
        }
        
        
        return str_replace('{{fillableConfig}}',
            $configs,
            $modelFileContents
        );
    }
    
    /**
     * Given the model file contents, put in the guarded config in appropriate 
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array  $guardedConfig      guarded config array
     * @return string                    The updated model file contents
     */
    private function addGuardedIfNeeded($modelFileContents, $guardedConfig)
    {
        $configs = '';
        if (!is_array($guardedConfig) || empty($guardedConfig))
        {
            
        }
        elseif($guardedConfig['createGuarded'])
        {
            if($guardedConfig['allGuarded'] == true)
            {
                $configs .= 'protected $guarded = array'."('*'); "."\n";
            }
            else
            {
                $configs .= 'protected $guarded = array'."(".implode(',',array_map(function($item){ return "'".$item."'"; }, $guardedConfig['defaultGuarded']))."); "."\n";
            }
            
        }
        
        
        return str_replace('{{guardedConfig}}',
            $configs,
            $modelFileContents
        );
    }
    
    /**
     * Given the model file contents, put in the hidden config in appropriate 
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array  $hiddenConfig      hidden config array
     * @return string                    The updated model file contents
     */
    private function addHiddenIfNeeded($modelFileContents, $hiddenConfig)
    {
        $configs = '';
        if (!is_array($hiddenConfig) || empty($hiddenConfig))
        {
            
        }
        elseif($hiddenConfig['createHidden'])
        {
            if($hiddenConfig['allHidden'] == true)
            {
                $configs .= 'protected $hidden = array'."('*'); "."\n";
            }
            else
            {
                $configs .= 'protected $hidden = array'."(".implode(',',array_map(function($item){ return "'".$item."'"; }, $hiddenConfig['defaultHidden']))."); "."\n";
            }
            
        }
        
        
        return str_replace('{{hiddenConfig}}',
            $configs,
            $modelFileContents
        );
    }
    
    /**
     * Given the model file contents, put in the visable config in appropriate 
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array  $visableConfig      visable config array
     * @return string                    The updated model file contents
     */
    private function addVisableIfNeeded($modelFileContents, $visableConfig)
    {
        $configs = '';
        if (!is_array($visableConfig) || empty($visableConfig))
        {
            
        }
        elseif($visableConfig['createVisable'])
        {
            if($visableConfig['allVisable'] == true)
            {
                $configs .= 'protected $visable = array'."('*'); "."\n";
            }
            else
            {
                $configs .= 'protected $visable = array'."(".implode(',',array_map(function($item){ return "'".$item."'"; }, $visableConfig['defaultVisable']))."); "."\n";
            }
            
        }
        
        
        return str_replace('{{visableConfig}}',
            $configs,
            $modelFileContents
        );
    }

    
     /**
     * Given the model file contents, put in the event hooks stub in appropriate 
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array  $exposeHooks       Hooks array
     * @return string                    The updated model file contents
     */
    private function addHooksIfNeeded($modelFileContents, $exposeHooks)
    {
        if (!is_array($exposeHooks) || empty($exposeHooks))
        {
            $hooks = '';
        }
        else
        {
            $template = $this->hookTemplate;

            $hooks = '';
            
            foreach($exposeHooks as $hookName=>$exposeHook)
            {
                if($exposeHook) $hooks .= str_replace('{{methodName}}', $hookName, $template) . " \n";
            }
            
        }

        return str_replace('{{eventHooks}}',
            $hooks,
            $modelFileContents
        );
    }
    
      /**
     * Given the model file contents, put in the Ardent config in appropriate 
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  array  $ardentConfig      Ardent config array
     * @return string                    The updated model file contents
     */
    private function addArdentConfigIfNeeded($modelFileContents, $ardentConfig)
    {
        if (!is_array($ardentConfig) || empty($ardentConfig))
        {
            $configs = '';
        }
        else
        {
            
            $configs = '';
            if($ardentConfig['autoHydrate'] != false)   $configs .= 'public $autoHydrateEntityFromInput = true; '."\n";
            if($ardentConfig['autoPurge'] != false)     $configs .= 'public $autoPurgeRedundantAttributes = true; '."\n";
            
            if(!empty($ardentConfig['hashFields']))     $configs .= 'public static $passwordAttributes  = array'."(".implode(',',array_map(function($item){ return "'".$item."'"; }, $ardentConfig['hashFields']))."); "."\n";
            if($ardentConfig['autoHash'] != false)      $configs .= 'public $autoHashPasswordAttributes = true; '." \n";
        }

        
        return str_replace('{{ardentConfig}}',
            $configs,
            $modelFileContents
        );
    }
    
    
    
    /**
     * Given the model file contents, put in the model name in the appropriate
     * location
     * @param  string $modelFileContents The contents of the model file
     * @param  string $modelName         The name of the model
     * @return string                    The updated model file contents
     */
    private function addModelName($modelFileContents, $modelName)
    {
        return str_replace('{{modelName}}', $modelName, $modelFileContents);
    }


    /**
     * Given the model file contents, put in the timestamps in the appropriate
     * location if needed
     * @param  string  $modelFileContents The contents of the model file
     * @param  boolean $timestamps        Whether timestamps are needed
     * @return string                    The updated model file contents
     */
    private function addTimestampsIfNeeded($modelFileContents, $timestamps)
    {
        // Always explicitly set the timestamps field to true or false
        if ($timestamps)
        {
            $t = 'public $timestamps = true;';
        }
        else
        {
            $t = 'public $timestamps = false;';
        }

        return str_replace('{{timestamps}}',
            $t,
            $modelFileContents
        );
    }


    /**
     * Given the model file contents, put in the softDelete in the appropriate
     * location if needed
     * @param  string  $modelFileContents The contents of the model file
     * @param  boolean $timestamps        Whether softDelete is needed
     * @return string                     The updated model file contents
     */
    private function addSoftDeletesIfNeeded($modelFileContents, $softDeletes)
    {
        // If softDeletes is enabled, add in the line to enable it, else remove
        // the tag. We set this only when true
        if ($softDeletes)
        {
            return str_replace('{{softDeletes}}',
                "protected \$softDelete = true;",
                $modelFileContents
            );
        }

        // Else, add in the primary key line overriding the defaults
        else
        {
            return str_replace("    {{softDeletes}}\n", '', $modelFileContents);
        }
    }


    /**
     * Given the model file contents, put in the primary key override in the appropriate
     * location if needed
     * @param  string  $modelFileContents The contents of the model file
     * @param  string $primaryKey         The primary key of the model
     * @return string                     The updated model file contents
     */
    private function addPrimaryKeyIfNeeded($modelFileContents, $primaryKey)
    {
        // If the primary key is id, simply remove the primary key line along
        // with its newline
        if ($primaryKey == 'id')
        {
            return str_replace("    {{primaryKey}}\n", '', $modelFileContents);
        }

        // Else, add in the primary key line overriding the defaults
        else
        {
            return str_replace('{{primaryKey}}',
                "public \$primaryKey = '{$primaryKey}';",
                $modelFileContents
            );
        }
    }


    /**
     * Given the model file contents, put in the table name override in the appropriate
     * location if needed
     * @param  string  $modelFileContents The contents of the model file
     * @param  string  $tableName         The table name override or blank
     * @return string                     The updated model file contents
     */
    private function addTableNameIfNeeded($modelFileContents, $tableName)
    {
        // If the model has a table name, it means that it was overriden,
        // so put in a table name line
        if ($tableName)
        {
            return str_replace('{{tableName}}',
                "protected \$table = '{$tableName}';",
                $modelFileContents
            );
        }

        // Else remove the line
        else
        {
            return str_replace("    {{tableName}}\n", '', $modelFileContents);
        }
    }


    /**
     * Given a function name and all the data related to it, generate the relation
     * function block with all the necessary parameters
     * @param  string $functionName The name of the function
     * @param  array  $functionData All the meta data related to the function
     * @return string               The relation function block
     */
    private function getRelationFunction($functionName, $functionData)
    {
        // Store the template locally
        $result = $this->relationalFunctionTemplate;

        // Add in the function name
        $result = str_replace('{{functionName}}', $functionName, $result);

        // If the relation type if mt, then the function has no parameters
        // So just whip it up here and return, since this is the only odd one
        // out
        if ($functionData['relationType'] == 'mt')
        {
            return str_replace(
                '{{functionBody}}',
                'return $this->morphTo();',
                $result
            );
        }

        // Create the function body
        // We begin with:
        // return $this->function('Model'
        $functionBody = 'return $this->'
            . $this->getFunctionNameFromRelationType($functionData['relationType'])
            . "('" . $functionData['toModel'] . "'" ;

        // Add in any extra parameters
        // For belongs to many, we have the table name override first, and then
        // the foreign keys. For everything else, there is just one foreign key
        //
        // We'll arrive at one of the following:
        // return $this->function('Model', 'foreignKey'
        // return $this->function('Model', 'pivotTable'
        // return $this->function('Model', 'pivotTable', 'foreignKey1', 'foreignKey2'
        //
        // First, check if it is a belongsToMany (btm or btmc)
        if (in_array($functionData['relationType'], array('btm','btmc')))
        {
            // Check if a pivot table is provided
            if ($functionData['pivotTable'])
            {
                // Add the pivot table first
                $functionBody .= ", '" . $functionData['pivotTable'] . "'";

                // Now check if we also have the two foreign keys
                if ($functionData['foreignKey'])
                {
                    // Add the two foreign keys as well
                    $functionBody .= ", '" . $functionData['foreignKey'][0] . "'"
                        . ", '" . $functionData['foreignKey'][1] . "'";
                }
            }
        }

        // For all other relations, check if a foreign key is override is present, and
        // append it
        else
        {
            if ($functionData['foreignKey'])
            {
                $functionBody .= ", '" . $functionData['foreignKey'] . "'";
            }
        }


        // Close the parenthesis
        $functionBody .= ')';

        // Check if the relation is btmc, and has additional fields that should
        // be added
        if (isset($functionData['additional']['btmcColumns'])
            and $functionData['additional']['btmcColumns'])
        {
            $functionBody .= "->withPivot('"
                . implode("', '", $functionData['additional']['btmcColumns'])
                . "')";
        }

        // If the relation is btmc and the migration has timestamps enabled, add
        // the withTimestamps caluse
        if (isset($functionData['additional']['btmcTimestamps'])
            and $functionData['additional']['btmcTimestamps'])
        {
            $functionBody .= '->withTimestamps()';
        }

        // Add a semicolon
        $functionBody .= ';';

        // Add the function body to the function template
        $result = str_replace('{{functionBody}}', $functionBody, $result);


        // Return the final function block
        return $result;
    }


    /**
     * Given a relation type code, get the function name as it goes inside
     * Eloquent
     * @param  string $relationType The relation type code
     * @return string               The function name of the relation as in Eloquent
     */
    private function getFunctionNameFromRelationType($relationType)
    {
        switch ($relationType)
        {
            case 'ho':
                return 'hasOne';

            case 'hm':
                return 'hasMany';

            case 'bt':
                return 'belongsTo';

            case 'mo':
                return 'morphOne';

            case 'mm':
                return 'morphMany';

            case 'mt':
                return 'morphTo';

            case 'btm':
            case 'btmc':
                return 'belongsToMany';
        }
    }


    /**
     * Add the given function block at the appropriate location of the model file
     * template
     * @param  string $modelFileContents The model file contents
     * @param  string $functionBlock     The generated function block
     * @return string                    The new model file contents
     */
    private function addRelationFunction($modelFileContents, $functionBlock)
    {
        return str_replace('    {{relationalFunctions}}',
            "{$functionBlock}\n    {{relationalFunctions}}",
            $modelFileContents
        );
    }


    /**
     * Remove the relation function placeholder tag from the model contents
     * @param  string $modelFileContents The model file contents
     * @return string                    The new model file contents
     */
    private function removeRelationFunctionTag($modelFileContents)
    {
        return str_replace("\n" . '    {{relationalFunctions}}',
            '',
            $modelFileContents
        );
    }
}
