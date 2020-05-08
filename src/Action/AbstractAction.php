<?php declare(strict_types = 1);

namespace MediaServer\Action;

abstract class AbstractAction implements ActionInterface
{
	protected $config;

	public function __construct(?array $config)
	{
		$this->config = $config;
	}
}