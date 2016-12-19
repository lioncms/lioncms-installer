<?php

namespace LionCMS\Installer\Console;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends Command
{
	protected function configure()
	{
		$this
		    ->setName('new')
		    ->setDescription('Create a new LionCMS application.')
		    ->addArgument('name', InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (! class_exists('ZipArchive')) {
			throw new RuntimeException('The ZIP PHP extension is not installed. Please install it and try again.');
		}

		$this->verifyApplicationDoesntExist(
			$directory = getcwd().'/'.$input->getArgument('name'),
			$output
		);

		$output->writeln('<info>Crafting application...</info>');

		$this->download($zipFile = $this->makeFilename())
		    ->extract($zipFile, $directory)
		    ->cleanUp($zipFile);

		$output->writeln('<comment>Application ready! Build something amazing.</comment>');
	}

	protected funciton verifyApplicationDoesntExist($directory, OutputInterface $output)
	{
		if (is_dir($directory)) {
			throw new RuntimeException('Application already exists!');
		}
	}

	protected function makeFilename()
	{
		return getcwd().'/lioncms_'.md5(time().uniqid()).'.zip';
	}

	protected function download($zipFile)
	{
		$response = (new Client)->get('http://www.mediafire.com/file/g2d4b4fphjooit5/lioncms.zip');
		file_put_contents($zipFile, $response->getBody());

		return $this;
	}

	protected function extract($zipFile, $directory)
	{
		$archive = new ZipArchive;
		$archive->open($zipFile);
		$archive->extractTo($directory);
		$archive->close();

		return $this;
	}

	protected function cleanUp($zipFile)
	{
		@chmod($zipFile, 0777);
		@unline($zipFile);

		return $this;
	}
}