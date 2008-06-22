<?php

	abstract class UnitTest
	{
		// A function that does initializations needed for any of the tests to start.
		public function initialize()
		{
		}
		
		// A function that should return an array of tests for the class.
		// The array should consist of <ID> => <name> pairs.
		public function getTests()
		{
			return array();		
		}
		
		// Should return true on success or a string on failure.
		abstract public function doTest($testID);
		
		public function getTestDescription($testID)
		{
			// By default no description is available.
			return 'No description available';	
		}
		
	}

?>