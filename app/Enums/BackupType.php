<?php

namespace App\Enums;

enum BackupType: string
{
    case Full = 'full';
    case Database = 'database';
    case Files = 'files';
    case Incremental = 'incremental';

    public function label(): string
    {
        return match ($this) {
            self::Full => 'Full',
            self::Database => 'Database',
            self::Files => 'Files',
            self::Incremental => 'Incremental',
        };
    }
}
