<?php

namespace App\Shop\Import;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseImport
 *
 * @author michael.hampton
 */
class BaseImport {

    /**
     * Escape character
     *
     * @access protected
     * @var array
     */
    protected $escape = '\\';

    /**
     * Collection of required fields
     *
     * @access protected
     * @var array
     */
    protected $requiredFields = [];
    
    /**
     *
     * @var type 
     */
    protected $expectedHeaders = [];

    /**
     * Delimiter character
     *
     * @access protected
     * @var string
     */
    protected $delimiter = ',';

    /**
     * Enclosure character
     *
     * @access protected
     * @var string
     */
    protected $enclosure = '"';

    /**
     * Collection of validation errors
     *
     * @access protected
     * @var array
     */
    protected $arrErrors = [];

    public function __construct() {
        
    }

    /**
     * Given a file pointer resource, return the next row from the file
     *
     * @access public
     * @param Resource $handle
     * @return array|null|false
     * @throws \InvalidArgumentException If $handle is not a valid resource
     */
    protected function fgetcsv($handle) {
        $result = fgetcsv($handle, 0, $this->delimiter, $this->enclosure, $this->escape);
        if ($result === null) {
            throw new \Exception('File pointer resource used in fgetcsv is invalid');
        }
        return $result;
    }

    /**
     * Checks if arrays of fields in `$or` have at least one value present in `$fields`.
     *
     * @access protected
     * @param array $or
     * @param array $fields
     * @return boolean
     */
    protected function orFieldsValid(array $or, array $fields) {
        $valid = true;
        foreach ($or as $requiredFields) {
            $valid = count(array_intersect($requiredFields, $fields)) > 0;
            if (!$valid) {
                break;
            }
        }
        return $valid;
    }

    /**
     * Return the array of errors
     *
     * @access public
     * @param void
     * @return array
     */
    public function getErrors() {
        return $this->arrErrors;
    }

    /**
     * Logs missing required fields
     *
     * @access protected
     * @param array $row
     * @param array $and
     * @param array $or
     * @return void
     */
    protected function logMissingRequiredFields(array $row, array $and = [], array $or = []) {

        if (!empty($and)) {
            $required = implode('", "', array_diff($and, $row));

            if (!empty($required)) {
                $this->arrErrors[] = sprintf(
                        'The following missing columns are required: "%s".', $required
                );
            }
        }

        if (!empty($or)) {
            $logOrError = function($fields) use ($row) {
                $diff = array_diff($fields, $row);
                if (!count($diff)) {
                    $this->arrErrors[] = sprintf(
                            'At least one of the following columns is required: "%s".', implode($diff, '", "')
                    );
                }
            };

            array_walk($or, $logOrError->bindTo($this));
        }
    }

    /**
     * Normalizes the data in a row.
     *
     * @access protected
     * @param array $row
     * @return array
     */
    protected function normalizeRow(array $row) {
        return array_filter(array_map('trim', array_map('strtolower', $row)));
    }

    /**
     * Parses the first row
     *
     * Checks for duplicate column names and ensures all required fields are present
     *
     * @param array $data
     * @access protected
     * @return array $row normalized
     */
    protected function parseFirstRow(array $row) {
        $row = $this->normalizeRow($row);
        $duplicateKeys = array_diff_key($row, array_unique($row));

        if (!empty($duplicateKeys)) {
            $duplicateKeys = implode($duplicateKeys, '", "');
            $this->arrErrors[] = sprintf('The following columns are duplicated: "%s".', $duplicateKeys);
        }

        if (empty($this->arrErrors)) {
            $this->checkRequiredFields($row);
        }
        return $row;
    }

    /**
     * Checks if a rule for the $params['key'] exists and validates.
     *
     * Logs errors from the rule if invalid.
     *
     * @access protected
     * @param array $params ['key' => ?, 'value' => ?]
     * @return void
     * @throws  \LogicException If a mapped rule does not exist
     */
    protected function checkRule(array $params) {
        $value = trim($params['value']);
        $key = $params['key'];
  
        if(empty($value) && in_array($key, $this->requiredFields)) {
                        
            $this->arrErrors[] = $key . ' is a required field and cannot be empty';
        }
    }

    /**
     * Verifies that required fields are all present and logs errors if missing.
     *
     * @access protected
     * @param array $row
     * @return void
     */
    protected function checkRequiredFields(array $row) {

        $required = $this->expectedHeaders;

        // Fields that must all be present
        $and = array_filter($required, 'is_string');

        // Fields where at least one must be present
        $or = array_filter($required, 'is_array');

        /**
         * The following block checks if required fields are all present
         * and logs any errors errors
         */
        if (
        // number of fields is less than the required count
                count($row) < count($required) ||
                // $or fields are required, but not present
                (!empty($or) && !$this->orFieldsValid($or, $row)) ||
                // remaining fields are not present
                count(array_intersect($and, $row)) !== count($and)
        ) {
            $this->logMissingRequiredFields($row, $and, $or);
        }
    }

}
