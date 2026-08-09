<?php

declare(strict_types=1);

namespace Wajub\Resources;

use Wajub\Http\BaseClient;
use Wajub\Http\HttpUtils;
use Wajub\RequestOptions;

final class TaxResource extends BaseClient
{
    /** @return array<string, mixed> */
    public function getSettings(): array
    {
        return HttpUtils::pickResource($this->get('/tax/settings'), 'tax', 'settings');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function updateSettings(array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource($this->put('/tax/settings', $params, $options), 'tax', 'settings');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function rates(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/tax/rates', $params), 'rates', 'tax_rates');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function calculate(array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource($this->post('/tax/calculate', $params, $options), 'tax', 'calculation');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function reports(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/tax/reports', $params), 'reports', 'tax_reports');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function listCodes(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/tax/codes', $params), 'tax_codes', 'codes');
    }

    /** @return array<string, mixed> */
    public function retrieveCode(string $code): array
    {
        return HttpUtils::pickResource($this->get('/tax/codes/'.rawurlencode($code)), 'tax_code', 'code');
    }

    /**
     * @param  array<string, mixed>|null  $params
     */
    public function listRegistrations(?array $params = null): PagedResult
    {
        return Pagination::createPagedList($this, '/tax/registrations', 'registrations', $params);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createRegistration(array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->post('/tax/registrations', $params, $options),
            'registration',
            'tax_registration',
        );
    }

    /** @return array<string, mixed> */
    public function retrieveRegistration(string $id): array
    {
        return HttpUtils::pickResource(
            $this->get('/tax/registrations/'.rawurlencode($id)),
            'registration',
            'tax_registration',
        );
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function updateRegistration(string $id, array $params, ?RequestOptions $options = null): array
    {
        return HttpUtils::pickResource(
            $this->put('/tax/registrations/'.rawurlencode($id), $params, $options),
            'registration',
            'tax_registration',
        );
    }

    public function deleteRegistration(string $id, ?RequestOptions $options = null): void
    {
        $this->del('/tax/registrations/'.rawurlencode($id), $options);
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function jurisdictions(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/tax/jurisdictions', $params), 'jurisdictions', 'tax_jurisdictions');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function thresholds(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/tax/thresholds', $params), 'thresholds', 'tax_thresholds');
    }

    /**
     * @param  array<string, mixed>|null  $params
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, mixed>|null}
     */
    public function thresholdAlerts(?array $params = null): array
    {
        return HttpUtils::pickList($this->get('/tax/thresholds/alerts', $params), 'alerts', 'threshold_alerts');
    }
}
