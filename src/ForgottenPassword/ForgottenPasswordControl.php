<?php
declare(strict_types=1);


namespace Ludoi\Components\ForgottenPassword;

use Ludoi\Utils\Users\Users;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

class ForgottenPasswordControl extends Control
{
	private Users $users;
	/** @var Presenter */
	private Presenter $presenter;
	private ?\Closure $actionOnSubmit = null;

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

	protected function createComponentForgottenForm(): Form
	{
		$form = new Form;
		$form->addText('username', 'Uživatel:')
			->setRequired('Zadej uživatele.');

		$form->addSubmit('send', 'Odeslat');

		$form->onSuccess[] = [$this, 'forgottenFormSubmitted'];

		$form->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));

		return $form;
	}

	public function forgottenFormSubmitted(Form $form): void
	{
		try {
			$values = $form->getValues();
			if (is_null($user = $this->users->getUser($values->username))) {
				$this->presenter->flashMessage('Uživatel nenalezen', 'danger');
			} else if (!$user->active) {
				$this->presenter->flashMessage('Uživatel nenalezen', 'danger');
			} else {
				$initial = $this->users->forgottenPasswordInRow($user);
				if (!is_null($this->actionOnSubmit))
					$this->actionOnSubmit($user);
			}
			$this->redirect('this');
		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/ForgottenPasswordControl.latte');
		$this->template->render();
	}
}