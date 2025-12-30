<?php


class OCRService
{
    public function processReceipt(string $imagePath): array
    {
        return $this->simulateOCRResult();
    }

    private function simulateOCRResult(): array
    {
        $stores = [
            ['name' => 'Biedronka', 'items' => $this->getBiedronkaItems()],
            ['name' => 'Lidl', 'items' => $this->getLidlItems()],
            ['name' => 'Żabka', 'items' => $this->getZabkaItems()],
            ['name' => 'Rossmann', 'items' => $this->getRossmannItems()],
            ['name' => 'Kaufland', 'items' => $this->getKauflandItems()],
        ];

        $selected = $stores[array_rand($stores)];
        $items = $selected['items'];
        
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * ($item['quantity'] ?? 1);
        }

        return [
            'store_name' => $selected['name'],
            'date' => date('Y-m-d'),
            'items' => $items,
            'total_amount' => round($total, 2),
            'confidence' => rand(75, 95) / 100,
            'raw_text' => $this->generateRawText($selected['name'], $items, $total),
            'needs_review' => true
        ];
    }

    private function getBiedronkaItems(): array
    {
        return [
            ['name' => 'Chleb tostowy', 'price' => 4.99, 'quantity' => 1],
            ['name' => 'Mleko 2% 1L', 'price' => 3.49, 'quantity' => 2],
            ['name' => 'Masło extra', 'price' => 7.99, 'quantity' => 1],
            ['name' => 'Ser żółty Gouda', 'price' => 12.99, 'quantity' => 1],
            ['name' => 'Jabłka Gala 1kg', 'price' => 5.99, 'quantity' => 1],
        ];
    }

    private function getLidlItems(): array
    {
        return [
            ['name' => 'Pierś z kurczaka', 'price' => 18.99, 'quantity' => 1],
            ['name' => 'Pomidory malinowe', 'price' => 8.99, 'quantity' => 1],
            ['name' => 'Jogurt naturalny', 'price' => 2.49, 'quantity' => 3],
            ['name' => 'Makaron penne', 'price' => 4.29, 'quantity' => 1],
            ['name' => 'Oliwa z oliwek', 'price' => 24.99, 'quantity' => 1],
        ];
    }

    private function getZabkaItems(): array
    {
        return [
            ['name' => 'Kawa mrożona', 'price' => 6.99, 'quantity' => 1],
            ['name' => 'Kanapka z szynką', 'price' => 8.49, 'quantity' => 1],
            ['name' => 'Woda mineralna 0.5L', 'price' => 2.99, 'quantity' => 2],
            ['name' => 'Baton czekoladowy', 'price' => 3.99, 'quantity' => 1],
        ];
    }

    private function getRossmannItems(): array
    {
        return [
            ['name' => 'Szampon do włosów', 'price' => 15.99, 'quantity' => 1],
            ['name' => 'Pasta do zębów', 'price' => 8.99, 'quantity' => 1],
            ['name' => 'Krem do rąk', 'price' => 12.49, 'quantity' => 1],
            ['name' => 'Dezodorant spray', 'price' => 14.99, 'quantity' => 1],
        ];
    }

    private function getKauflandItems(): array
    {
        return [
            ['name' => 'Mięso mielone 500g', 'price' => 14.99, 'quantity' => 1],
            ['name' => 'Ryż basmati 1kg', 'price' => 8.99, 'quantity' => 1],
            ['name' => 'Cukier biały 1kg', 'price' => 4.49, 'quantity' => 1],
            ['name' => 'Olej rzepakowy 1L', 'price' => 9.99, 'quantity' => 1],
            ['name' => 'Mąka tortowa 1kg', 'price' => 3.99, 'quantity' => 2],
            ['name' => 'Jajka M 10szt', 'price' => 11.99, 'quantity' => 1],
        ];
    }

    private function generateRawText(string $storeName, array $items, float $total): string
    {
        $text = strtoupper($storeName) . "\n";
        $text .= "NIP: " . rand(100, 999) . "-" . rand(10, 99) . "-" . rand(10, 99) . "-" . rand(100, 999) . "\n";
        $text .= date('d.m.Y H:i') . "\n";
        $text .= str_repeat('-', 30) . "\n";
        
        foreach ($items as $item) {
            $qty = $item['quantity'] ?? 1;
            $price = $item['price'];
            $text .= sprintf("%-20s %d x %.2f\n", substr($item['name'], 0, 20), $qty, $price);
        }
        
        $text .= str_repeat('-', 30) . "\n";
        $text .= sprintf("SUMA: %.2f PLN\n", $total);
        $text .= "\nDZIĘKUJEMY ZA ZAKUPY!";
        
        return $text;
    }
}