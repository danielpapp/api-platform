<?php

namespace Tests\Application;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class ApiTestCase extends WebTestCase
{
    use Factories, ResetDatabase;

    /** @var array<string, string> */
    protected static array $formats = [
        'json' => 'application/json',
        'yaml' => 'application/x-yaml',
    ];

    /**
     * @return iterable<string, array{string}>
     */
    public static function supportedFormats(): iterable
    {
        foreach (self::$formats as $name => $format) {
            yield sprintf('Testing %s format (%s)', $name, $format) => [$format];
        }
    }

    protected static function createApiClient(string $format): KernelBrowser
    {
        self::ensureKernelShutdown();

        return self::createClient(server: [
            'HTTP_ACCEPT' => $format,
            'CONTENT_TYPE' => $format,
        ]);
    }

    protected static function get(string $format, string $uri): Crawler
    {
        return self::createApiClient($format)->request('GET', $uri);
    }

    /**
     * @param array<mixed> $data
     */
    protected static function post(string $format, string $uri, array $data): Crawler
    {
        return self::createApiClient($format)->request('POST', $uri, content: self::serialize($format, $data));
    }

    /**
     * @return array<mixed>
     */
    protected static function getResponseData(string $format): array
    {
        /** @var Response $response */
        $response = self::getClient()->getResponse();

        return self::deserialize($format, (string)$response->getContent());
    }

    /**
     * @param array<mixed> $expectedData
     */
    protected static function assertResponseDataSame(string $format, array $expectedData): void
    {
        self::assertSame($expectedData, self::getResponseData($format));
    }

    /**
     * @param array<mixed> $expectedItems
     */
    protected static function assertPaginationItemsSame(string $format, array $expectedItems): void
    {
        $lastItem = end($expectedItems);
        $expectedNextCursor = $lastItem === false ? null : base64_encode((string)$lastItem['id']);

        self::assertResponseDataSame($format, ['items' => $expectedItems, 'nextCursor' => $expectedNextCursor]);
    }

    /**
     * @param array<mixed> $content
     */
    private static function serialize(string $format, array $content): string
    {
        return match ($format) {
            self::$formats['json'] => json_encode($content, JSON_THROW_ON_ERROR),
            self::$formats['yaml'] => Yaml::dump($content),
            default => throw new \InvalidArgumentException("Unsupported format: $format"),
        };
    }

    /**
     * @return array<mixed>
     */
    private static function deserialize(string $format, string $value): array
    {
        return match ($format) {
            self::$formats['json'] => json_decode($value, true, 512, JSON_THROW_ON_ERROR),
            self::$formats['yaml'] => Yaml::parse($value),
            default => throw new \InvalidArgumentException("Unsupported format: $format"),
        };
    }
}
