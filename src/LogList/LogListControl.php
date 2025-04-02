<?php
declare(strict_types=1);


namespace Ludoi\Components\LogList;

use DateInterval;
use Nette\Application\UI\Control;
use Nette\Utils\DateTime;
use Tracy\Dumper;

class LogListControl extends Control
{
	private string $folder;
	private int $fileSizeLarge;
	private int $fileSizeMedium;
	private int $fileTimeNew;
	private int $fileTimeMedium;

	public function setFolder(string $folder): void
	{
		$this->folder = $folder;
	}

	private function getFiles(): array
	{
		$timeMedium = (new DateTime())->sub(new DateInterval("PT{$this->fileTimeMedium}H"))->getTimestamp();
		$timeNew = (new DateTime())->sub(new DateInterval("PT{$this->fileTimeNew}H"))->getTimestamp();
		$files = [];
		foreach (array_diff(scandir($this->folder, SCANDIR_SORT_ASCENDING), ['..', '.']) as $fileName) {
			$fileWithPath = $this->folder . $fileName;
			$filesize = @filesize($fileWithPath);
			$filemtime = @filemtime($fileWithPath);
			$files[] = ['name' => $fileName, 'size' => $filesize, 'mtime' => $filemtime,
				'class-size' => ($filesize > $this->fileSizeLarge)? 'text-danger' : (($filesize > $this->fileSizeMedium)? 'text-warning' : 'text-muted'),
				'class-time' => ($filemtime > $timeNew)? 'text-danger' : (($filemtime > $timeMedium)? 'text-warning' : 'text-muted')];
		}
		return $files;
	}

	public function render(int $fileSizeLarge = 5000000, int $fileSizeMedium = 1000000,
						   int $fileTimeNew = 6, int $fileTimeMedium = 24): void
	{
		$this->fileSizeLarge = $fileSizeLarge;
		$this->fileSizeMedium = $fileSizeMedium;
		$this->fileTimeNew = $fileTimeNew;
		$this->fileTimeMedium = $fileTimeMedium;

		$this->template->files = $this->getFiles();
		$this->template->render(__DIR__ . '/LogListControl.latte');
	}

	public function handleOpen(string $log)
	{
		$response = $this->getPresenter()->getHttpResponse();
		$filename = $this->folder . $log;
		$content = file_get_contents($filename);
		if (!str_contains($log, '.html')) {
			$response->setContentType('text/plain', 'UTF-8');
			echo $content;
		} else {
			$response->setContentType('text/html', 'UTF-8');
			echo $content;
		}
		$this->getPresenter()->terminate();
	}

}