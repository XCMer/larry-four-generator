<?php namespace LarryFour\Parser;

class FieldParser
{
    public function parse($line)
    {
        // Explode the data into spaces
        //
        // There will be three parts:
        // - The first part can be a name of a nameless type
        // - The second part is the name of the field
        // - The third part is the additional parameter to the field
        //   that can be parsed differently depending on the field,
        //   which is why we're keeping it unmodified for now
        $data = explode(" ", $line, 3);

        // See if the field is that of timestamps
        if (strtolower($data[0]) == 'timestamps')
        {
            return array(
                'type' => 'timestamps'
            );
        }

        // Else, it has to be a normal field
        $name = $data[0]; // The name of the field
        $type = $data[1]; // The type of the field

        // The parameters are separated by spaces for a normal field, but by a
        // CSV styled value for the enum type, which can be different types of
        // strings
        if ($type == 'enum')
        {
            // Remove extraneous spaces around the commas carefully
            $parameters = preg_replace( "/\s*,\s*/", ",", $data[2] );

            // Then parse it as a CSV
            $parameters = str_getcsv( trim( $parameters ) );
        }
        else
        {
            $parameters = isset($data[2]) ? explode(" ", trim($data[2])) : array();
        }


        // Return the final set of parameters
        return array(
            'name' => $name,
            'type' => $type,
            'parameters' => $parameters
        );
    }
}