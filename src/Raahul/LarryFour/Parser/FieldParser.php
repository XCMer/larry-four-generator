<?php namespace Raahul\LarryFour\Parser;

use \Raahul\LarryFour\Exception\ParseError;

class FieldParser
{
    public function parse($line)
    {
        // Get a list of segments on the current line
        $segments = $this->getLineSegments($line);

        // If we don't have any segments, return a blank array
        if (empty($segments)) return array();

        // Else, parse the first segment as the actual field details
        $fieldData = $this->parseFieldData($segments[0]);

        // Parse the other segments too and merge them with the fieldData
        for ($i=1; $i<count($segments); $i++)
        {
            $result = $this->parseFieldModifier($segments[$i]);
            $fieldData = array_merge($fieldData, $result);
        }

        // Return the field data only for now
        return $fieldData;
    }


    /**
     * Returns an array of commands found in a line that are separated
     * by a semicolon. Though the first segment is always about the field,
     * the subsequent segments are field modifiers that specify things like
     * default value, nullable, and other field properties.
     *
     * @param string $line The complete field line
     *
     * @return array An array of all segments as strings
     */
    public function getLineSegments($line)
    {
        // We have to take care of semicolons appearing inside quotes
        // and stuffs like that

        // Add imaginary semicolons at the beginning and the end of the
        // string for reference purpose
        $line = ";{$line};";

        // Some C style parsing to the rescue
        $insideQuotes = false;
        $semicolonPositions = array();

        $length = strlen($line);
        for ($i=0; $i<$length; $i++)
        {
            // If we have either of the quote character, set insideQuotes
            // to that quote character. We need to keep track of which
            // character started to quote so that the same character
            // dequotes it as well
            if (in_array($line[$i], array('"', "'")))
            {
                // If a quote is open and it is equal to the current
                // quote character, then dequote
                if ($insideQuotes and ($insideQuotes == $line[$i]))
                {
                    $insideQuotes = false;
                }
                // Else if it's a quote and we are not inside a quote yet
                else
                {
                    $insideQuotes = $line[$i];
                }

                // In both cases, go to next character
                continue;
            }

            // If we have a semicolon, push its position only if we are not
            // inside a quote
            if (!$insideQuotes and ($line[$i] == ';'))
            {
                $semicolonPositions[] = $i;
            }
        }

        // Initialize a list of segments
        $segments = array();

        // Now that we have the position of all semicolons that are actual
        // separators, we'll explode the string manually at those points
        $semicolonPositionsCount = count($semicolonPositions);
        for ($i=1; $i<$semicolonPositionsCount; $i++)
        {
            // The start of the substring excluding the semicolon
            $s = $semicolonPositions[$i - 1] + 1;

            // the length of the substring ignoring the semicolon
            $l = $semicolonPositions[$i] - $s;

            // Get the substring and trim it, and add it to the list
            // of segments
            $subs = trim( substr($line, $s, $l) );

            // To filter out blank substrings
            if ($subs) $segments[] = $subs;
        }

        return $segments;
    }


    /**
     * Parses the first segment of the field data to determine the
     * type of the field, the name of the field, and also special
     * handling for fields like enums
     *
     * @param  string $fieldSegment The first segment of the field line
     * @return array                The name, type and additional parameters of a field
     */
    public function parseFieldData($fieldSegment)
    {
        // Explode the data into spaces
        //
        // There will be three parts:
        // - The first part can be a name of a nameless type
        // - The second part is the name of the field
        // - The third part is the additional parameter to the field
        //   that can be parsed differently depending on the field,
        //   which is why we're keeping it unmodified for now
        $data = explode(" ", $fieldSegment, 3);

        // See if the field is that of timestamps or softDeletes
        if ( in_array( $data[0], array('timestamps', 'softDeletes') ) )
        {
            return array(
                'type' => $data[0]
            );
        }

        // Else, it has to be a normal field
        // Check if we have sufficient parameters
        if (count($data) < 2){
            throw new ParseError("Field does not have type provided: {$fieldSegment}");
        }

        $name = $data[0]; // The name of the field
        $type = $data[1]; // The type of the field

        // Check for validity of the type field
        if (
            !in_array($type, array(
                'increments', 'string', 'integer', 'bigInteger', 'smallInteger',
                'float', 'decimal', 'boolean', 'date', 'dateTime', 'time', 'timestamp',
                'text', 'binary', 'enum'
            ))
        )
        {
            throw new ParseError("Invalid field type: {$type}");
        }

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

        // Additional error checks on parameters
        // Decimal field needs two parameters
        if ($type == 'decimal' && (count($parameters) < 2))
        {
            throw new ParseError("Decimal field requires two parameters, precision and scale: {$fieldSegment}");
        }


        // Return the final set of parameters
        return array(
            'name' => $name,
            'type' => $type,
            'parameters' => $parameters
        );
    }


    public function parseFieldModifier($segment)
    {
        // Parse field modifiers as csv with space as the delimiter,
        // which will allow quotes around things like the default value
        $parsedSegment = str_getcsv($segment, " ");

        // If we don't have any segments, return an empty array
        if (empty($parsedSegment)) return array();

        // Else, we'll start parsing
        $command = $parsedSegment[0];
        if ($command == 'default')
        {
            return array(
                'default' => isset($parsedSegment[1]) ? $parsedSegment[1] : ""
            );
        }
        else if ($command == 'nullable')
        {
            return array('nullable' => true);
        }
        else if ($command == 'unsigned')
        {
            return array('unsigned' => true);
        }
        else if ($command == 'primary')
        {
            return array('primary' => true);
        }
        else if ($command == 'fulltext')
        {
            return array('fulltext' => true);
        }
        else if ($command == 'unique')
        {
            return array('unique' => true);
        }
        else if ($command == 'index')
        {
            return array('index' => true);
        }


        // If nothing matches, it's an error
        throw new ParseError("Invalid field modifier: {$segment}");
    }
}