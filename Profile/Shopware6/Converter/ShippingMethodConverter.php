<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Profile\Shopware6\Converter;

use SwagMigrationAssistant\Migration\Converter\ConvertStruct;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\Logging\LoggingServiceInterface;
use SwagMigrationAssistant\Migration\Media\MediaFileServiceInterface;
use SwagMigrationAssistant\Profile\Shopware6\Mapping\Shopware6MappingServiceInterface;

abstract class ShippingMethodConverter extends ShopwareConverter
{
    /**
     * @var MediaFileServiceInterface
     */
    protected $mediaFileService;

    public function __construct(
        Shopware6MappingServiceInterface $mappingService,
        LoggingServiceInterface $loggingService,
        MediaFileServiceInterface $mediaFileService
    ) {
        parent::__construct($mappingService, $loggingService);
        $this->mediaFileService = $mediaFileService;
    }

    public function getSourceIdentifier(array $data): string
    {
        return $data['id'];
    }

    public function getMediaUuids(array $converted): ?array
    {
        $mediaIds = [];
        foreach ($converted as $manufacturer) {
            if (isset($manufacturer['media']['id'])) {
                $mediaIds[] = $manufacturer['media']['id'];
            }
        }

        return $mediaIds;
    }

    protected function convertData(array $data): ConvertStruct
    {
        $converted = $data;

        $this->mainMapping = $this->getOrCreateMappingMainCompleteFacade(
            DefaultEntities::SHIPPING_METHOD,
            $data['id'],
            $converted['id']
        );

        $this->updateAssociationIds(
            $converted['translations'],
            DefaultEntities::LANGUAGE,
            'languageId',
            DefaultEntities::NUMBER_RANGE
        );

        if (isset($converted['prices'])) {
            foreach ($converted['prices'] as &$price) {
                $this->updateAssociationIds(
                    $price['currencyPrice'],
                    DefaultEntities::CURRENCY,
                    'currencyId',
                    DefaultEntities::PRODUCT
                );
            }
            unset($price);
        }

        if (isset($data['taxId'])) {
            $converted['taxId'] = $this->getMappingIdFacade(DefaultEntities::TAX, $data['taxId']);
        }

        if (isset($converted['media'])) {
            $this->updateMediaAssociation($converted['media']);
        }

        return new ConvertStruct($converted, null, $this->mainMapping['id'] ?? null);
    }
}
