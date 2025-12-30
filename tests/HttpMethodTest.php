<?php

require_once __DIR__ . '/../src/attributes/HttpMethod.php';
require_once __DIR__ . '/../src/attributes/AttributeValidator.php';


class SimpleTestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
            echo "✓ PASS: {$message}\n";
        } else {
            $this->failed++;
            $this->failures[] = $message;
            echo "✗ FAIL: {$message}\n";
        }
    }

    public function assertEquals($expected, $actual, string $message): void
    {
        $this->assert($expected === $actual, $message . " (oczekiwano: " . var_export($expected, true) . ", otrzymano: " . var_export($actual, true) . ")");
    }

    public function assertTrue(bool $value, string $message): void
    {
        $this->assert($value === true, $message);
    }

    public function assertFalse(bool $value, string $message): void
    {
        $this->assert($value === false, $message);
    }

    public function printSummary(): void
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "PODSUMOWANIE TESTÓW\n";
        echo str_repeat("=", 50) . "\n";
        echo "Zaliczone: {$this->passed}\n";
        echo "Niezaliczone: {$this->failed}\n";
        
        if (!empty($this->failures)) {
            echo "\nNiezaliczone testy:\n";
            foreach ($this->failures as $failure) {
                echo "  - {$failure}\n";
            }
        }
        
        echo str_repeat("=", 50) . "\n";
    }
}

class TestController
{
    #[HttpMethod(['GET'])]
    public function getOnly(): void {}

    #[HttpMethod(['POST'])]
    public function postOnly(): void {}

    #[HttpMethod(['GET', 'POST'])]
    public function getAndPost(): void {}

    #[HttpMethod(['GET', 'POST', 'PUT', 'DELETE'])]
    public function allMethods(): void {}

    public function noAttribute(): void {}
}


echo "============================================\n";
echo "TESTY JEDNOSTKOWE - Atrybut HttpMethod\n";
echo "============================================\n\n";

$runner = new SimpleTestRunner();

echo "--- TEST 1: Konstruktor i getMethods() ---\n";

$httpMethod = new HttpMethod(['GET', 'POST']);
$methods = $httpMethod->getMethods();

$runner->assertEquals(2, count($methods), "Atrybut powinien przechowywać 2 metody");
$runner->assertTrue(in_array('GET', $methods), "Atrybut powinien zawierać metodę GET");
$runner->assertTrue(in_array('POST', $methods), "Atrybut powinien zawierać metodę POST");

$httpMethodLower = new HttpMethod(['get', 'post']);
$methodsNormalized = $httpMethodLower->getMethods();
$runner->assertTrue(in_array('GET', $methodsNormalized), "Metody powinny być znormalizowane do wielkich liter (get -> GET)");

echo "\n--- TEST 2: isMethodAllowed() ---\n";

$httpMethod = new HttpMethod(['GET', 'POST']);

$runner->assertTrue($httpMethod->isMethodAllowed('GET'), "Metoda GET powinna być dozwolona");
$runner->assertTrue($httpMethod->isMethodAllowed('POST'), "Metoda POST powinna być dozwolona");
$runner->assertTrue($httpMethod->isMethodAllowed('get'), "Metoda 'get' (małe litery) powinna być dozwolona");
$runner->assertFalse($httpMethod->isMethodAllowed('PUT'), "Metoda PUT nie powinna być dozwolona");
$runner->assertFalse($httpMethod->isMethodAllowed('DELETE'), "Metoda DELETE nie powinna być dozwolona");

echo "\n--- TEST 3: AttributeValidator::validateHttpMethod() ---\n";

$resultGet = AttributeValidator::validateHttpMethod('TestController', 'getOnly', 'GET');
$runner->assertTrue($resultGet['allowed'], "GET powinien być dozwolony dla metody getOnly()");

$resultGetFail = AttributeValidator::validateHttpMethod('TestController', 'getOnly', 'POST');
$runner->assertFalse($resultGetFail['allowed'], "POST nie powinien być dozwolony dla metody getOnly()");

$resultPost = AttributeValidator::validateHttpMethod('TestController', 'postOnly', 'POST');
$runner->assertTrue($resultPost['allowed'], "POST powinien być dozwolony dla metody postOnly()");

$resultMulti = AttributeValidator::validateHttpMethod('TestController', 'getAndPost', 'GET');
$runner->assertTrue($resultMulti['allowed'], "GET powinien być dozwolony dla metody getAndPost()");

$resultMultiPost = AttributeValidator::validateHttpMethod('TestController', 'getAndPost', 'POST');
$runner->assertTrue($resultMultiPost['allowed'], "POST powinien być dozwolony dla metody getAndPost()");

$resultNoAttr = AttributeValidator::validateHttpMethod('TestController', 'noAttribute', 'DELETE');
$runner->assertTrue($resultNoAttr['allowed'], "DELETE powinien być dozwolony dla metody bez atrybutu");

echo "\n--- TEST 4: Sprawdzenie getAllowedMethodsString() ---\n";

$httpMethod = new HttpMethod(['GET', 'POST', 'PUT']);
$methodsString = $httpMethod->getAllowedMethodsString();
$runner->assertEquals('GET, POST, PUT', $methodsString, "getAllowedMethodsString() powinien zwrócić prawidłowy string");

echo "\n--- TEST 5: hasHttpMethodAttribute() ---\n";

$hasAttr = AttributeValidator::hasHttpMethodAttribute('TestController', 'getOnly');
$runner->assertTrue($hasAttr, "Metoda getOnly() powinna mieć atrybut HttpMethod");

$noAttr = AttributeValidator::hasHttpMethodAttribute('TestController', 'noAttribute');
$runner->assertFalse($noAttr, "Metoda noAttribute() nie powinna mieć atrybutu HttpMethod");

$runner->printSummary();
