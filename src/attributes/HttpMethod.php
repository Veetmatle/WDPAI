<?php

/**
 * Atrybut HttpMethod
 * 
 * Określa dozwolone metody HTTP dla metody kontrolera.
 * Używany do deklaratywnej kontroli dostępu do akcji kontrolera.
 * 
 * Przykład użycia:
 * #[HttpMethod(['GET'])]
 * public function index(): void { ... }
 * 
 * #[HttpMethod(['GET', 'POST'])]
 * public function login(): void { ... }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class HttpMethod
{
    /**
     * @var array<string> Tablica dozwolonych metod HTTP
     */
    private array $methods;

    /**
     * Konstruktor atrybutu
     * 
     * @param array<string> $methods Dozwolone metody HTTP (np. ['GET', 'POST'])
     */
    public function __construct(array $methods)
    {
        $this->methods = array_map('strtoupper', $methods);
    }

    /**
     * Pobierz dozwolone metody HTTP
     * 
     * @return array<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Sprawdź czy podana metoda HTTP jest dozwolona
     * 
     * @param string $method Metoda HTTP do sprawdzenia
     * @return bool
     */
    public function isMethodAllowed(string $method): bool
    {
        return in_array(strtoupper($method), $this->methods, true);
    }

    /**
     * Pobierz dozwolone metody jako string (do wyświetlania w błędach)
     * 
     * @return string
     */
    public function getAllowedMethodsString(): string
    {
        return implode(', ', $this->methods);
    }
}
