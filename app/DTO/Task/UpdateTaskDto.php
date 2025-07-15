<?php

namespace App\DTO\Task;

class UpdateTaskDto
{
    public string $title;
    public ?string $description;
    public ?string $status;
    public ?int $user_id;
    public ?int $priority_id;

    public function __construct(array $data) 
    {
        $this->title = $data['title'];
        $this->description = $data['description'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->user_id = $data['user_id'] ?? null;
        $this->priority_id = $data['priority_id'] ?? null;
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'priority_id' => $this->priority_id,
        ], fn($value) => $value !== null);
    }
}
