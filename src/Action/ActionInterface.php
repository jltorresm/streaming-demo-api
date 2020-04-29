<?php declare(strict_types=1);

namespace MediaServer\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ActionInterface
{
	public function run(Request $request): Response;
}