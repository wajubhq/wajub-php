<?php

declare(strict_types=1);

namespace Wajub\Tests;

use Wajub\Exception\InvalidRequestException;
use Wajub\Exception\WajubError;
use Wajub\Http\HttpUtils;

final class HttpUtilsTest extends TestCase
{
    public function test_normalize_api_key_strips_bearer_prefix(): void
    {
        $this->assertSame('sk_test.abc', HttpUtils::normalizeApiKey('Bearer sk_test.abc'));
        $this->assertSame('pk_test.abc', HttpUtils::normalizeApiKey('bearer pk_test.abc'));
        $this->assertSame('sk_test.abc', HttpUtils::normalizeApiKey('sk_test.abc'));
    }

    public function test_pick_resource_returns_first_matching_key(): void
    {
        $body = ['transaction' => ['id' => 'pay_123', 'amount' => 5000]];

        $this->assertSame('pay_123', HttpUtils::pickResource($body, 'transaction')['id']);
    }

    public function test_pick_resource_falls_back_to_body(): void
    {
        $body = ['id' => 'pay_123'];

        $this->assertSame('pay_123', HttpUtils::pickResource($body, 'transaction')['id']);
    }

    public function test_pick_list_extracts_plural_key_and_meta(): void
    {
        $body = [
            'refunds' => [['id' => 'ref_1']],
            'meta' => ['current_page' => 1, 'last_page' => 1],
        ];

        $picked = HttpUtils::pickList($body, 'refunds');

        $this->assertSame('ref_1', $picked['data'][0]['id']);
        $this->assertSame(1, $picked['meta']['current_page']);
    }

    public function test_pick_list_falls_back_to_data_key(): void
    {
        $body = ['data' => [['id' => 'x']], 'meta' => ['total' => 1]];

        $picked = HttpUtils::pickList($body, 'missing');

        $this->assertSame('x', $picked['data'][0]['id']);
    }

    public function test_create_idempotency_key_has_prefix(): void
    {
        $this->assertStringStartsWith('wajub-', HttpUtils::createIdempotencyKey());
        $this->assertStringStartsWith('custom-', HttpUtils::createIdempotencyKey('custom'));
    }

    public function test_error_from_response_builds_wajub_error(): void
    {
        $error = HttpUtils::errorFromResponse(422, [
            'message' => 'Validation failed',
            'code' => 'validation_error',
            'errors' => ['amount' => ['Must be positive']],
        ]);

        $this->assertInstanceOf(InvalidRequestException::class, $error);
        $this->assertSame('Validation failed', $error->getMessage());
        $this->assertSame('validation_error', $error->errorCode);
        $this->assertSame(422, $error->httpStatus);
        $this->assertSame('Must be positive', $error->errors['amount']);
    }
}
