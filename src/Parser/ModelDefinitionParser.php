<?php namespace LarryFour\Parser;

use \LarryFour\Exception\ParseError;

class ModelDefinitionParser
{
    public function parse($line)
    {
        // Get all the segments of this model definition line
        $segments = $this->getLineSegments($line);

        // If we don't have any segments, return an empty array
        if (empty($segments)) return array();

        // Now, the first segment is the actual definition of the model, while
        // the subsequent ones are the relations
        $modelData = $this->parseModelData($segments[0]);

        // Now, parse each subsequent section for relations and merge it with
        // the model data that we already have
        $modelData['relations'] = array();

        for ($i=1; $i<count($segments); $i++)
        {
            $modelData['relations'][] = $this->parseModelRelations($segments[$i]);
        }

        // Return the final data
        return $modelData;
    }


    /**
     * Gets all segments of a model definition line separated by a semicolon
     * @param  string $line The model line to be parsed
     * @return array        The resultant ordered array of segments
     */
    private function getLineSegments($line)
    {
        $segments = explode(";", $line);
        $segments = array_map('trim', $segments);
        $segments = array_filter($segments);

        return $segments;
    }


    /**
     * Parses the first segment of the model definition to get the model name
     * and the table name override if any
     * @param  string $segment The first segment of the model definition
     * @return array           An array containing the model and table name
     */
    private function parseModelData($segment)
    {
        $data = explode(" ", trim($segment));

        // Check if we have a model name
        if (!$data)
        {
            throw new ParseError("Model name cannot be blank");
        }

        $modelName = $data[0];
        $tableName = isset($data[1]) ? $data[1] : '';

        return array(
            'modelName' => $modelName,
            'tableName' => $tableName
        );
    }


    /**
     * Parses the subsequent segments of the model definitions looking for
     * relations
     * @param  string $segment A relation segment of the model definition
     * @return array           The relation type and the related model
     */
    private function parseModelRelations($segment)
    {
        $data = explode(" ", $segment);

        // If there is no data, throw an error
        if (empty($data) or (count($data) < 2))
        {
            throw new ParseError("Insufficient parameters for relation: " . $segment);
        }

        // The first part is the relation type while the second part is the
        // related model. We'll throw an error if an invalid relation type is being
        // specified
        $relationType = trim($data[0]);
        if (!in_array( $relationType, array('ho', 'hm', 'bt', 'btm', 'mm', 'mo') ))
        {
            throw new ParseError("Invalid relation type: " . $relationType);
        }

        $parsedData = array(
            'relatedModel' => trim($data[1]),
            'relationType' => $relationType,
            'pivotTable' => '',
            'foreignKey' => '',
        );

        // The third part is the foreign key override for all tables but belongs
        // to many.
        if ($parsedData['relationType'] == 'btm')
        {
            // If only the table name is being overridden
            if (count($data) == 3)
            {
                $parsedData['pivotTable'] = trim($data[2]);
            }
            // If the columns names are as well
            else if (count($data) == 5)
            {
                $parsedData['pivotTable'] = trim($data[2]);
                $parsedData['foreignKey'] = array(
                    trim($data[3]),
                    trim($data[4])
                );
            }
            // Else if count is not even 2, then there is a syntax error
            else if (count($data) != 2)
            {
                throw new ParseError("Belongs to many relation needs none or both foreign keys present, but found just one: " . $segment);
            }
        }
        // For all other cases, simple set the foreignKey parameter if present
        // else set it to blank
        else
        {
            $parsedData['foreignKey'] = isset($data[2]) ? trim($data[2]) : '';
        }

        // Polymorphic relations need a compulsory third foreign key parameter
        if (in_array( $parsedData['relationType'], array('mm', 'mo') ))
        {
            if (!$parsedData['foreignKey'])
            {
                throw new ParseError("Polymorphic relations require foreign key to be specified: " . $segment);
            }
        }

        return $parsedData;
    }
}