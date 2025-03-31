<?php
declare(strict_types=1);


namespace Ludoi\Components\Login;

use Ludoi\Utils\Logger\Logger;
use Ludoi\Utils\Logger\LoggerChannel;
use Ludoi\Utils\Users\Users;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

class LoginControl extends Control {

	/** @var Presenter */
	private Presenter $presenter;
	private ?LoggerChannel $loggerChannel = null;
	private Users $users;

	public function __construct(Users $users)
	{
		$this->users = $users;
	}

	public function setPresenter($presenter): void
	{
		$this->presenter = $presenter;
	}

	public function setLoggerChannel(LoggerChannel $loggerChannel): void
	{
		$this->loggerChannel = $loggerChannel;
	}

	protected function createComponentLoginForm(): Form {
		$form = new Form();
		$form->addText('username', 'Uživatel')->setRequired();
		$form->addPassword('password', 'Heslo')->setRequired();
		$form->addCheckbox('remember', 'Zapamatuj si mě na tomto počítači');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = [$this, 'processForm'];

		$form->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));

		return $form;
	}

	public function processForm(Form $form): void {
		$values = $form->getValues();
		if ($values->remember) {
			$this->presenter->getUser()->setExpiration('+ 14 days', false);
		} else {
			$this->presenter->getUser()->setExpiration('+ 20 minutes', true);
		}
		$user = $this->users->normalizeUserName($values->username);
		try {
			$this->presenter->getUser()->login($user, $values->password);
			$this->users->updateLastLogin($user);
			$this->loggerChannel?->addInfo('Logged in', $this->getPresenter()->getUser()->id);
			if (isset($this->presenter->backlink)) {
				$this->presenter->restoreRequest($this->presenter->backlink);
				$this->presenter->redirect(':Front:Homepage:');
			}
		} catch (AuthenticationException $e) {
			$this->loggerChannel?->addError('Unsuccessful login', $user);
			$form->addError($e->getMessage());
		}
	}

	public function render(): void {
		$this->template->setFile(__DIR__ . '/LoginControl.latte');
		$this->template->render();
	}

	function logMessage(Logger $logger): void
	{
		// TODO: Implement logMessage() method.
	}
}
