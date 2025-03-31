<?php
declare(strict_types=1);


namespace Ludoi\Components\ForgottenUser;

interface ForgottenUserControlFactory
{
	public function create(): ForgottenUserControl;
}