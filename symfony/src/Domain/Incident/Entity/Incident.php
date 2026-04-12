<?php
declare(strict_types=1);

namespace App\Domain\Incident\Entity;

use App\Domain\Incident\ValueObject\IncidentId;
use App\Domain\Incident\ValueObject\IncidentStatus;

final class Incident
{
    private function __construct(
        private IncidentId $id,
        private string $userId,
        private string $category,
        private string $subject,
        private string $message,
        private IncidentStatus $status,
    ) {
    }

    public static function create(
        string $userId,
        string $category,
        string $subject,
        string $message,
    ): self {
        $category = strtoupper(trim($category));
        $subject = trim($subject);
        $message = trim($message);

        if ($category === '') {
            throw new \InvalidArgumentException('Category is required.');
        }
        if ($subject === '') {
            throw new \InvalidArgumentException('Subject is required.');
        }
        if (mb_strlen($subject) > 120) {
            throw new \InvalidArgumentException('Subject cannot exceed 120 chars.');
        }
        if ($message === '') {
            throw new \InvalidArgumentException('Message is required.');
        }
        if (mb_strlen($message) > 2000) {
            throw new \InvalidArgumentException('Message cannot exceed 2000 chars.');
        }

        return new self(
            IncidentId::new(),
            $userId,
            $category,
            $subject,
            $message,
            IncidentStatus::OPEN,
        );
    }

    public function id(): IncidentId
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function status(): IncidentStatus
    {
        return $this->status;
    }
}
