<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\CarbonImmutable;
use Petersons\D2L\AuthenticatedUriFactory;
use PHPUnit\Framework\TestCase;

final class AuthenticatedUriFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function testCreatingAuthenticatedUriWithoutQueryString(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-05-24 00:00:00'));

        $authenticatedUriFactory = new AuthenticatedUriFactory(
            'https://learn.petersons.com',
            'foo',
            'bar',
            'baz',
            'qux',
        );

        $uri = $authenticatedUriFactory->createAuthenticatedUri(
            'https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/download/1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1',
            'GET'
        );

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/download/1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1?x_a=foo&x_b=baz&x_c=0YDdguqihIMXTLrE55bngQ7zoJKpl-AIgGXv-RnZDqw&x_d=2H0LCBp4MoGCulRk2Gvr8eaTxPb2OziPwZ2aSs_aRS0&x_t=1621814400',
            $uri
        );
    }

    public function testCreatingAuthenticatedUriWithQueryString(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2021-05-24 00:00:00'));

        $authenticatedUriFactory = new AuthenticatedUriFactory(
            'https://learn.petersons.com',
            'foo',
            'bar',
            'baz',
            'qux',
        );

        $uri = $authenticatedUriFactory->createAuthenticatedUri(
            'https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/download/1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1?foo=bar',
            'GET'
        );

        $this->assertSame(
            'https://learn.petersons.com/d2l/api/lp/1.30/dataexport/bds/download/1fa8ff9c-8702-46fc-a863-18ca6c2cc4d1?x_a=foo&x_b=baz&x_c=0YDdguqihIMXTLrE55bngQ7zoJKpl-AIgGXv-RnZDqw&x_d=2H0LCBp4MoGCulRk2Gvr8eaTxPb2OziPwZ2aSs_aRS0&x_t=1621814400&foo=bar',
            $uri
        );
    }
}
