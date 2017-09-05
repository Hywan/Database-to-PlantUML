<?php

declare(strict_types=1);

namespace Hywan\DatabaseToPlantUML\Frontend\PgSQL;

use Hywan\DatabaseToPlantUML\Frontend;
use PDO;

class Reader extends Frontend\Reader
{
    public function read(string $databaseName): Frontend\Database
    {
        return new Database($this->getDatabaseConnection(), $databaseName);
    }
}
