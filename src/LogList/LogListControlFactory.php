<?php
declare(strict_types=1);


namespace Ludoi\Components\LogList;

interface LogListControlFactory
{
	public function create(): LogListControl;
}