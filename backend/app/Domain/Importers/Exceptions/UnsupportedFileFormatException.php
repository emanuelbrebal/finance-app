<?php

namespace App\Domain\Importers\Exceptions;

use RuntimeException;

class UnsupportedFileFormatException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The uploaded file format is not supported. Please upload an OFX or CSV file.');
    }
}
