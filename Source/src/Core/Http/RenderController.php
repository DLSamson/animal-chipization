<?php

namespace Api\Core\Http;

use Fenom;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class RenderController extends BaseController implements ControllerInterface
{
    protected Fenom $fenom;

    public function __construct(LoggerInterface $log, ValidatorInterface $validator, Fenom $fenom)
    {
        $this->fenom = $fenom;
        parent::__construct($log, $validator);
    }

    protected function render(string $template_name, $vars)
    {
        return $this->fenom->fetch($template_name . '.tpl', $vars);
    }
}