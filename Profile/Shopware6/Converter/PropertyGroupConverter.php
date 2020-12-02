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

abstract class PropertyGroupConverter extends ShopwareConverter
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
        foreach ($converted as $group) {
            if (!isset($group['options'])) {
                continue;
            }

            foreach ($group['options'] as $option) {
                if (isset($option['media']['id'])) {
                    $mediaIds[] = $option['media']['id'];
                }
            }
        }

        return $mediaIds;
    }

    protected function convertData(array $data): ConvertStruct
    {
        $converted = $data;

        $this->mainMapping = $this->getOrCreateMappingMainCompleteFacade(
            DefaultEntities::PROPERTY_GROUP,
            $data['id'],
            $converted['id']
        );

        $this->updateAssociationIds(
            $converted['translations'],
            DefaultEntities::LANGUAGE,
            'languageId',
            DefaultEntities::PROPERTY_GROUP
        );

        foreach (\array_keys($converted['options']) as $key) {
            $this->convertOption($converted['options'][$key]);
        }

        return new ConvertStruct($converted, null, $this->mainMapping['id'] ?? null);
    }

    protected function convertOption(array &$option): void
    {
        $this->updateAssociationIds(
            $option['translations'],
            DefaultEntities::LANGUAGE,
            'languageId',
            DefaultEntities::PROPERTY_GROUP
        );

        if (isset($option['media'])) {
            $this->updateMediaAssociation($option['media']);
        }
    }
}
