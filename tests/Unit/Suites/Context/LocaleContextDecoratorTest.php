<?php


namespace Brera\Context;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\Context\LocaleContextDecorator
 * @covers \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpRequest
 */
class LocaleContextDecoratorTest extends ContextDecoratorTestAbstract
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return 'locale';
    }

    /**
     * @return mixed[]
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-locale'];
    }

    /**
     * @param Context $stubContext
     * @param mixed[] $stubContextData
     * @return LocaleContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new LocaleContextDecorator($stubContext, $stubContextData);
    }

    /**
     * @param string $urlString
     * @return HttpRequest
     */
    private function createTestRequest($urlString)
    {
        return HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString($urlString),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
    }

    public function testExceptionIsThrownIfNeitherLocaleNorRequestArePresent()
    {
        $this->setExpectedException(
            UnableToDetermineLocaleException::class,
            'Unable to determine locale from context source data ("locale" and "request" not present)'
        );
        $decorator = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), []);
        $decorator->getValue($this->getDecoratorUnderTestCode());
    }

    /**
     * @param mixed[] $sourceData
     * @param string $expected
     * @dataProvider localeSourceDataProvider
     */
    public function testItReturnsTheExpectedLocale(array $sourceData, $expected)
    {
        $localeContext = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), $sourceData);
        $this->assertSame($expected, $localeContext->getValue(LocaleContextDecorator::CODE));
    }

    /**
     * @return array[]
     */
    public function localeSourceDataProvider()
    {
        return [
            'locale' => [['locale' => 'xxx'], 'xxx'],
            'request de' => [['request' => $this->createTestRequest('http://example.com/xx_de')], 'de_DE'],
            'request en' => [['request' => $this->createTestRequest('http://example.com/xx_en')], 'en_US'],
            'default' => [['request' => $this->createTestRequest('http://example.com/')], 'de_DE'],
            'missing lang' => [['request' => $this->createTestRequest('http://example.com/xx')], 'de_DE'],
            'invalid lang' => [['request' => $this->createTestRequest('http://example.com/xx_xx')], 'de_DE'],
            'locale and request' => [
                ['request' => $this->createTestRequest('http://example.com/xx_en'), 'locale' => 'xxx'],
                'xxx'
            ],
        ];
    }
}