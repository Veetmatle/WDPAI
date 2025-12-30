<?php

class ReceiptItem 
{
    private int $id;
    private int $receiptId;
    private string $productName;
    private ?int $categoryId;
    private float $price;
    private int $quantity;
    private ?string $createdAt;

    public function __construct(
        int $id,
        int $receiptId,
        string $productName,
        float $price,
        ?int $categoryId = null,
        int $quantity = 1,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->receiptId = $receiptId;
        $this->productName = $productName;
        $this->categoryId = $categoryId;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt;
    }

    public function getId(): int 
    {
        return $this->id;
    }

    public function getReceiptId(): int 
    {
        return $this->receiptId;
    }

    public function getProductName(): string 
    {
        return $this->productName;
    }

    public function getCategoryId(): ?int 
    {
        return $this->categoryId;
    }

    public function getPrice(): float 
    {
        return $this->price;
    }

    public function getQuantity(): int 
    {
        return $this->quantity;
    }

    public function getTotalPrice(): float 
    {
        return $this->price * $this->quantity;
    }

    public function getCreatedAt(): ?string 
    {
        return $this->createdAt;
    }

    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'receipt_id' => $this->receiptId,
            'product_name' => $this->productName,
            'category_id' => $this->categoryId,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'total_price' => $this->getTotalPrice(),
            'created_at' => $this->createdAt
        ];
    }
}