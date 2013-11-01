<?php

/**
 * Part of the FuelPHP framework.
 *
 * @package   Fuel\Validation
 * @version   2.0
 * @license   MIT License
 * @copyright 2010 - 2013 Fuel Development Team
 */

namespace Fuel\Validation;

use Fuel\Validation\Exception\InvalidField;

/**
 * Main entry point for the validation functionality. Handles registering validation rules and loading validation
 * adaptors.
 *
 * @package Fuel\Validation
 * @author  Fuel Development Team
 */
class Validation
{

	/**
	 * @var string[RuleInterface[]]
	 */
	protected $rules = array();

	/**
	 * Set to true if the run() function has been called
	 *
	 * @var bool
	 */
	protected $hasRun = false;

	/**
	 * Contains any validation messages.
	 *
	 * @var string[]
	 */
	protected $messages = array();

	/**
	 * Resets the class to a fresh state, as if it had not been run
	 */
	public function reset()
	{
		$this->hasRun = false;
		$this->messages = array();
	}

	/**
	 * Adds a rule that can be used to validate a field
	 *
	 * @param string        $field
	 * @param RuleInterface $rule
	 *
	 * @return $this
	 */
	public function addRule($field, RuleInterface $rule)
	{
		$this->rules[$field][] = $rule;

		return $this;
	}

	/**
	 * Adds a new field to the validation object
	 *
	 * @param string $field
	 *
	 * @return $this
	 */
	public function addField($field)
	{
		$this->rules[$field] = array();

		return $this;
	}

	/**
	 * Returns a list of all known validation rules for a given field.
	 *
	 * @param string $field Name of the field to get rules for, or null for all fields
	 *
	 * @throws InvalidField
	 *
	 * @return RuleInterface[]|string[RuleInterface[]]
	 */
	public function getRules($field = null)
	{
		// Check if we are fetching a specific field or all
		if ( ! is_null($field))
		{
			// Now we know we have a field check that we know about it
			if (array_key_exists($field, $this->rules))
			{
				// It's a known field so grab the rules for it
				$results = $this->rules[$field];
			}
			// If not throw an exception
			else
			{
				throw new InvalidField($field);
			}
		}
		else
		{
			// No field was specified so return all the fields' rules
			$results = $this->rules;
		}

		return $results;
	}

	/**
	 * Takes an array of data and validates that against the assigned rules.
	 * The array is expected to have keys named after fields.
	 * This function will call reset() before it runs.
	 *
	 * @param array $data
	 *
	 * @return bool True if all the fields validated
	 */
	public function run(array $data)
	{
		// Make sure we are in a fresh state
		$this->reset();

		$result = true;

		foreach ($data as $fieldName => $value)
		{
			$fieldResult = $this->validateField($fieldName, $value);

			if ( ! $fieldResult)
			{
				// Only update the actual result if there was a failure
				// This means that a later "true" value does not override a previous
				// "false" value.
				$result = false;
			}
		}

		$this->hasRun = true;

		return $result;
	}

	/**
	 * Validates a single field
	 *
	 * @param string $field
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	protected function validateField($field, $value)
	{
		$rules = $this->getRules($field);

		$result = true;

		foreach ($rules as $rule)
		{
			$result = $rule->validate($value);

			if ( ! $result)
			{
				// Make sure the message is collected
				$this->messages[$field] = $rule->getMessage();

				break;
			}
		}

		return $result;
	}

	/**
	 * Returns true if validation has been run
	 *
	 * @return bool
	 */
	public function hasRun()
	{
		return $this->hasRun;
	}

	/**
	 * @return string[]
	 */
	public function getMessages()
	{
		return $this->messages;
	}

}
