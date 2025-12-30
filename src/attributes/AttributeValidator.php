<?php

require_once __DIR__ . '/HttpMethod.php';

class AttributeValidator
{
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

            if (empty($attributes)) {
                return $result;
            }

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
            $result['message'] = 'Nie można zwalidować metody: ' . $e->getMessage();
        }

        return $result;
    }

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
