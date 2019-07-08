<?php declare(strict_types=1);

namespace SwagMigrationAssistant\Profile\Shopware55\Gateway\Api;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Currency\CurrencyEntity;
use SwagMigrationAssistant\Migration\EnvironmentInformation;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Migration\Profile\ReaderInterface;
use SwagMigrationAssistant\Profile\Shopware55\Gateway\Shopware55GatewayInterface;
use SwagMigrationAssistant\Profile\Shopware55\Gateway\TableCountReaderInterface;
use SwagMigrationAssistant\Profile\Shopware55\Gateway\TableReaderInterface;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;

class Shopware55ApiGateway implements Shopware55GatewayInterface
{
    public const GATEWAY_NAME = 'api';

    /**
     * @var ReaderInterface
     */
    private $apiReader;

    /**
     * @var ReaderInterface
     */
    private $environmentReader;

    /**
     * @var TableReaderInterface
     */
    private $tableReader;

    /**
     * @var TableCountReaderInterface
     */
    private $tableCountReader;

    /**
     * @var EntityRepositoryInterface
     */
    private $currencyRepository;

    public function __construct(
        ReaderInterface $apiReader,
        ReaderInterface $environmentReader,
        TableReaderInterface $tableReader,
        TableCountReaderInterface $tableCountReader,
        EntityRepositoryInterface $currencyRepository
    ) {
        $this->apiReader = $apiReader;
        $this->environmentReader = $environmentReader;
        $this->tableReader = $tableReader;
        $this->tableCountReader = $tableCountReader;
        $this->currencyRepository = $currencyRepository;
    }

    public function getName(): string
    {
        return self::GATEWAY_NAME;
    }

    public function supports(MigrationContextInterface $migrationContext): bool
    {
        return $migrationContext->getConnection()->getProfileName() === Shopware55Profile::PROFILE_NAME;
    }

    public function read(MigrationContextInterface $migrationContext): array
    {
        return $this->apiReader->read($migrationContext);
    }

    public function readEnvironmentInformation(MigrationContextInterface $migrationContext): EnvironmentInformation
    {
        $environmentData = $this->environmentReader->read($migrationContext);
        $environmentDataArray = $environmentData['environmentInformation'];

        if (empty($environmentDataArray)) {
            return new EnvironmentInformation(
                Shopware55Profile::SOURCE_SYSTEM_NAME,
                Shopware55Profile::SOURCE_SYSTEM_VERSION,
                '',
                [],
                [],
                $environmentData['requestStatus']
            );
        }

        if (!isset($environmentDataArray['translations'])) {
            $environmentDataArray['translations'] = 0;
        }

        $updateAvailable = false;
        if (isset($environmentData['environmentInformation']['updateAvailable'])) {
            $updateAvailable = $environmentData['environmentInformation']['updateAvailable'];
        }

        /** @var CurrencyEntity $targetSystemCurrency */
        $targetSystemCurrency = $this->currencyRepository->search(new Criteria([Defaults::CURRENCY]), Context::createDefaultContext())->get(Defaults::CURRENCY);
        if (!isset($environmentDataArray['defaultCurrency'])) {
            $environmentDataArray['defaultCurrency'] = $targetSystemCurrency->getIsoCode();
        }

        $totals = $this->readTotals($migrationContext);
        $credentials = $migrationContext->getConnection()->getCredentialFields();

        return new EnvironmentInformation(
            Shopware55Profile::SOURCE_SYSTEM_NAME,
            $environmentDataArray['shopwareVersion'],
            $credentials['endpoint'],
            $totals,
            $environmentDataArray['additionalData'],
            $environmentData['requestStatus'],
            $updateAvailable,
            $targetSystemCurrency->getIsoCode(),
            $environmentDataArray['defaultCurrency']
        );
    }

    public function readTotals(MigrationContextInterface $migrationContext): array
    {
        return $this->tableCountReader->readTotals($migrationContext);
    }

    public function readTable(MigrationContextInterface $migrationContext, string $tableName, array $filter = []): array
    {
        return $this->tableReader->read($migrationContext, $tableName, $filter);
    }
}
