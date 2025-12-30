<?php

require_once __DIR__ . '/HttpMethod.php';

/**
 * Klasa do walidacji atrybutów kontrolerów
 * Sprawdza czy żądanie HTTP spełnia wymagania określone przez atrybuty
 */
class AttributeValidator
{
    /**
     * Sprawdź czy metoda HTTP jest dozwolona dla danej akcji kontrolera
     * 
     * @param string $controllerClass Nazwa klasy kontrolera
     * @param string $methodName Nazwa metody (akcji)
     * @param string $requestMethod Aktualna metoda HTTP żądania
     * @return array{allowed: bool, message: string, allowedMethods: array}
     */
    public static function validateHttpMethod(
        string $controllerClass, 
        string $methodName, 
        string $requestMethod
    ): array {
        $result = [
            'allowed' => true,
            'message' => '',
            'allowedMethods' => []
        ];

        try {
            $reflectionMethod = new ReflectionMethod($controllerClass, $methodName);
            $attributes = $reflectionMethod->getAttributes(HttpMethod::class);

            // Jeśli brak atrybutu HttpMethod, pozwól wszystkie metody
            if (empty($attributes)) {
                return $result;
            }

            // Pobierz pierwszy atrybut HttpMethod
            $httpMethodAttribute = $attributes[0]->newInstance();
            $allowedMethods = $httpMethodAttribute->getMethods();

            $result['allowedMethods'] = $allowedMethods;

            if (!$httpMethodAttribute->isMethodAllowed($requestMethod)) {
                $result['allowed'] = false;
                $result['message'] = sprintf(
                    'Metoda HTTP %s nie jest dozwolona dla tej akcji. Dozwolone: %s',
                    strtoupper($requestMethod),
                    $httpMethodAttribute->getAllowedMethodsString()
                );
            }
        } catch (ReflectionException $e) {
            // W przypadku błędu refleksji, dozwól żądanie (fail-open)
            $result['message'] = 'Nie można zwalidować metody: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Pobierz wszystkie atrybuty HttpMethod z metody kontrolera
     * 
     * @param string $controllerClass Nazwa klasy kontrolera
     * @param string $methodName Nazwa metody
     * @return array<HttpMethod>
     */
    public static function getHttpMethodAttributes(string $controllerClass, string $methodName): array
    {
        try {
            $reflectionMethod = new ReflectionMethod($controllerClass, $methodName);
            $attributes = $reflectionMethod->getAttributes(HttpMethod::class);
            
            return array_map(fn($attr) => $attr->newInstance(), $attributes);
        } catch (ReflectionException $e) {
            return [];
        }
    }

    /**
     * Sprawdź czy metoda kontrolera ma atrybut HttpMethod
     * 
     * @param string $controllerClass Nazwa klasy kontrolera
     * @param string $methodName Nazwa metody
     * @return bool
     */
    public static function hasHttpMethodAttribute(string $controllerClass, string $methodName): bool
    {
        try {
            $reflectionMethod = new ReflectionMethod($controllerClass, $methodName);
            return !empty($reflectionMethod->getAttributes(HttpMethod::class));
        } catch (ReflectionException $e) {
            return false;
        }
    }
}
