<?php declare(strict_types=1);

namespace MediaServer\Action;

abstract class AbstractAction implements ActionInterface
{
	protected ?array $config;

	public function __construct(?array $config)
	{
		$this->config = $config;
	}
}