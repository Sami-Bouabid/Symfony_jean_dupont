<?php

namespace App;

use App\Trait\TimeZone;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    use TimeZone;

    public function __construct(string $environment, bool $debug)
    {
        $this->setTimeZone("Europe/Paris");
        parent::__construct($environment, $debug);
    }
}
