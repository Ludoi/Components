<?php
declare(strict_types=1);


namespace Ludoi\Components\ForgottenUser;

use Ludoi\Utils\Users\Users;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;
use Nette\Utils\Strings;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

class ForgottenUserControl extends Control
{
	private Presenter $presenter;
	private ?\Closure $actionOnSubmit;

	public function __construct(Users $users)
	{
		$this->users = $users;
	}

	public function setPresenter($presenter)
	{
		$this->presenter = $presenter;
	}

	public function setActionOnSubmit(\Closure $actionOnSubmit): void
	{
		$this->actionOnSubmit = $actionOnSubmit;
	}

	public function createComponentForgottenUser(): Form
	{
		$form = new Form();
		$form->addEmail('email', 'Email:')
			->setRequired('Zadej email.')->setEmptyValue('@');

		$form->addSubmit('send', 'Odeslat');

		$form->onSuccess[] = [$this, 'forgottenFormSubmitted'];

		$form->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));

		return $form;
	}

	public function forgottenFormSubmitted(Form $form): void
	{
		try {
			$values = $form->getValues();
			$email = Strings::lower(Strings::trim($values->email));
			if (!is_null($this->actionOnSubmit))
				call_user_func($this->actionOnSubmit, $email);
			$this->redirect('this');
		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

	public function render(): void
	{
		$this->template->render(__DIR__ . '/ForgottenUserControl.latte');
	}

}