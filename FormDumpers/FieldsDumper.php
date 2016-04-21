<?php namespace janareit\laravel5generators\FormDumpers;

use janareit\laravel5generators\Migrations\SchemaParser;
use janareit\laravel5generators\Stub;

class FieldsDumper
{
    use StubTrait;

    /**
     * The form fields.
     *
     * @var string
     */
    protected $fields;

    /**
     * The constructor.
     *
     * @param string $fields
     */
    public function __construct($fields, $responsive)
    {
        $this->fields = $fields;
        $this->responsive = $responsive;
    }

    /**
     * Get schema parser.
     *
     * @return string
     */
    public function getParser()
    {
        return new SchemaParser($this->fields);
    }

    /**
     * Render the form.
     *
     * @return string
     */
    public function render()
    {
        $results = '';

        foreach ($this->getParser()->toArray() as $name => $types) {
            $results .= $this->getStub($this->getFieldType($types), $name).PHP_EOL;
        }

        return $results;
    }

    /**
     * Convert the fields to html heading.
     *
     * @return string
     */
    public function toHeading()
    {
        $results = '';

        foreach ($this->getParser()->toArray() as $name => $types) {
            if (in_array($name, $this->ignores)) {
                continue;
            }
            
            $results .= "\t\t\t".'<th>'.ucwords($name).'</th>'.PHP_EOL;
        }

        return $results;
    }

    /**
     * Convert the fields to formatted php script.
     *
     * @param string $var
     *
     * @return string
     */
    public function toBody($var)
    {
        $results = '';

        foreach ($this->getParser()->toArray() as $name => $types) {
            if (in_array($name, $this->ignores)) {
                continue;
            }

            $results .= "\t\t\t\t\t".'<td>{!! $'.$var.'->'.$name.' !!}</td>'.PHP_EOL;
        }

        return $results;
    }

    /**
     * Get replacements for $SHOW_BODY$.
     *
     * @param string $var
     *
     * @return string
     */
    public function toRows($var)
    {
        $results = PHP_EOL;

        foreach ($this->getParser()->toArray() as $name => $types) {
            if (in_array($name, $this->ignores)) {
                continue;
            }

            $scaffold_type = $this->responsive ? 'scaffold-responsive' : 'scaffold-table';

            $results .= Stub::createFromPath(__DIR__.'/../Stubs/'.$scaffold_type.'/row.stub', [
                'label' => ucwords($name),
                'column' => $name,
                'var' => $var,
            ])->render();
        }

        return $results.PHP_EOL;
    }
}
