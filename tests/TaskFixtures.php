<?php

class TestTask extends SMF_BackgroundTask
{
	public function execute()
	{
		echo  'nidoodle';
		return false;
	}
}
