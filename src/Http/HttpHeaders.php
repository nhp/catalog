<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\HeaderNotPresentException;
use LizardsAndPumpkins\Http\Exception\InvalidHttpHeadersException;

class HttpHeaders
{
    /**
     * @var string[]
     */
    private $headers;

    /**
     * @param string[] $headers
     */
    private function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param string[] $headers
     * @return HttpHeaders
     */
    public static function fromArray(array $headers)
    {
        $normalizedHeaders = [];

        foreach ($headers as $headerName => $headerValue) {
            if (!is_string($headerName) || !is_string($headerValue)) {
                throw new InvalidHttpHeadersException('Can only create HTTP headers from string');
            }

            $normalizedHeaderName = strtolower($headerName);
            $normalizedHeaders[$normalizedHeaderName] = $headerValue;
        }

        return new self($normalizedHeaders);
    }

    /**
     * @return HttpHeaders
     */
    public static function fromGlobalRequestHeaders()
    {
        $globalRequestHeaders = array_reduce(array_keys($_SERVER), function (array $result, $key) {
            return substr($key, 0, 5) !== 'HTTP_' ?
                $result :
                array_merge($result, [strtolower(str_replace('_', '-', substr($key, 5))) => $_SERVER[$key]]);
        }, []);

        return self::fromArray($globalRequestHeaders);
    }

    /**
     * @param string $headerName
     * @return string
     */
    public function get($headerName)
    {
        $normalizedHeaderName = strtolower($headerName);
        if (!$this->has($normalizedHeaderName)) {
            throw new HeaderNotPresentException(sprintf('The header "%s" is not present.', $headerName));
        }
        return $this->headers[$normalizedHeaderName];
    }

    /**
     * @return string[]
     */
    public function getAll()
    {
        return $this->headers;
    }

    /**
     * @param string $headerName
     * @return bool
     */
    public function has($headerName)
    {
        return array_key_exists(strtolower($headerName), $this->headers);
    }
}
