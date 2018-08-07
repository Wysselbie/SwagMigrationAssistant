<?php declare(strict_types=1);

namespace SwagMigrationNext\Command\Event;

use Symfony\Component\EventDispatcher\Event;

class MigrationAssetDownloadStartEvent extends Event
{
    public const EVENT_NAME = 'migration.asset.download.start';

    /**
     * @var int
     */
    private $numberOfFiles;

    public function __construct(int $numberOfFiles = 0)
    {
        $this->numberOfFiles = $numberOfFiles;
    }

    public function getNumberOfFiles(): int
    {
        return $this->numberOfFiles;
    }
}
