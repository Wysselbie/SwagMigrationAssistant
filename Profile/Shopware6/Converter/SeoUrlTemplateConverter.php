<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagMigrationAssistant\Profile\Shopware6\Converter;

use SwagMigrationAssistant\Migration\Converter\ConvertStruct;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;

abstract class SeoUrlTemplateConverter extends ShopwareConverter
{
    public function getSourceIdentifier(array $data): string
    {
        return $data['id'];
    }

    protected function convertData(array $data): ConvertStruct
    {
        $converted = $data;

        $seoUrlTemplateUuid = $this->mappingService->getSeoUrlTemplateUuid(
            $converted['id'],
            $converted['salesChannelId'] ?? null,
            $converted['routeName'],
            $this->migrationContext,
            $this->context
        );
        if ($seoUrlTemplateUuid !== null) {
            $converted['id'] = $seoUrlTemplateUuid;
        }

        $this->mainMapping = $this->getOrCreateMappingMainCompleteFacade(
            DefaultEntities::SEO_URL_TEMPLATE,
            $data['id'],
            $converted['id']
        );

        if (isset($converted['salesChannelId'])) {
            $converted['salesChannelId'] = $this->getMappingIdFacade(
                DefaultEntities::SALES_CHANNEL,
                $converted['salesChannelId']
            );
        }

        return new ConvertStruct($converted, null, $this->mainMapping['id'] ?? null);
    }
}
