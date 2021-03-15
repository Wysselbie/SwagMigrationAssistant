<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1615794849ChangeLengthsOfLoggingColumns extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1615794849;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('ALTER TABLE swag_migration_logging MODIFY `source_id` VARCHAR(255)');
        $connection->executeUpdate('ALTER TABLE swag_migration_logging MODIFY `entity` VARCHAR(255)');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}