<?php

declare(strict_types=1);

class TestTask extends SMF_BackgroundTask
{
	public function execute()
	{
		echo 'nidoodle';

		return false;
	}
}
