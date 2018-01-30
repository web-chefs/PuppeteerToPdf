<?php

namespace WebChefs\PuppeteerToPdf\Exceptions;

use Exception;

class CouldMakeTakePdf extends Exception
{
    public static function chromeOutputEmpty(string $screenShotPath)
    {
        return new static("For some reason Chrome did not write a file at `{$screenShotPath}`.");
    }
}
