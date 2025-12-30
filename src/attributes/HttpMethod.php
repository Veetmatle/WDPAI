<?php

#[Attribute(Attribute::TARGET_METHOD)]
class HttpMethod
{
    private array $methods;

    public function __construct(array $methods)
    {
        $this->methods = array_map('strtoupper', $methods);
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function isMethodAllowed(string $method): bool
    {
        return in_array(strtoupper($method), $this->methods, true);
    }

    public function getAllowedMethodsString(): string
    {
        return implode(', ', $this->methods);
    }
}
