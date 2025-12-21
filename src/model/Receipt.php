<?php

/**
 * Receipt Model
 * Represents a receipt/purchase entity
 */
class Receipt 
{
    private int $id;
    private int $userId;
    private string $storeName;
    private string $receiptDate;
    private float $totalAmount;
    private ?string $receiptImagePath;
    private ?string $notes;
    private ?string $createdAt;

    public function __construct(
        int $id,
        int $userId,
        string $storeName,
        string $receiptDate,
        float $totalAmount,
        ?string $receiptImagePath = null,
        ?string $notes = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->storeName = $storeName;
        $this->receiptDate = $receiptDate;
        $this->totalAmount = $totalAmount;
        $this->receiptImagePath = $receiptImagePath;
        $this->notes = $notes;
        $this->createdAt = $createdAt;
    }

    public function getId(): int 
    {
        return $this->id;
    }

    public function getUserId(): int 
    {
        return $this->userId;
    }

    public function getStoreName(): string 
    {
        return $this->storeName;
    }

    public function getReceiptDate(): string 
    {
        return $this->receiptDate;
    }

    public function getTotalAmount(): float 
    {
        return $this->totalAmount;
    }

    public function getReceiptImagePath(): ?string 
    {
        return $this->receiptImagePath;
    }

    public function getNotes(): ?string 
    {
        return $this->notes;
    }

    public function getCreatedAt(): ?string 
    {
        return $this->createdAt;
    }

    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'store_name' => $this->storeName,
            'receipt_date' => $this->receiptDate,
            'total_amount' => $this->totalAmount,
            'receipt_image_path' => $this->receiptImagePath,
            'notes' => $this->notes,
            'created_at' => $this->createdAt
        ];
    }
}