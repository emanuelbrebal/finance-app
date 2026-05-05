<?php

return [
    'importers' => [
        \App\Domain\Importers\OfxImporter::class,
        \App\Domain\Importers\NubankCsvImporter::class,
        \App\Domain\Importers\NubankCardCsvImporter::class,
        \App\Domain\Importers\GenericCsvImporter::class,
    ],
];
