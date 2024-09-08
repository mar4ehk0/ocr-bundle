<?php

namespace mar4ehk0\OCRBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OCRBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
