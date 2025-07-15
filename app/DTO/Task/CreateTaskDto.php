<?php

namespace App\DTO\Task;

class CreateTaskDto
{
    public string $title;
    public ?string $description;
    public string $status;
    public int $priority_id;
    public ?\Illuminate\Http\UploadedFile $image;
    public ?\DateTimeInterface $reminder_time;
    public ?int $reminder_before;
    public ?string $reminder_unit;

    public function __construct(array $data)
    {
        $this->title = $data['title'];
        $this->description = $data['description'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->priority_id = $data['priority_id'];
        $this->image = $data['image'] ?? null;
        $this->reminder_time = isset($data['reminder_time']) ? new \DateTime($data['reminder_time']) : null;
        $this->reminder_before = $data['reminder_before'];
        $this->reminder_unit = $data['reminder_unit'];
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority_id' => $this->priority_id,
            'image' => $this->image,
            'reminder_time' => $this->reminder_time?->format('Y-m-d H:i:s'),
            'reminder_before' => $this->reminder_before,
            'reminder_unit' => $this->reminder_unit,
        ];
    }
}