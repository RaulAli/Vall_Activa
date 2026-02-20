<?php
declare(strict_types=1);

namespace App\Presentation\Http\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class LoginRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 72)]
    public string $password = '';

    /** Optional device identifier forwarded by the client (UUID). */
    public ?string $deviceId = null;
}
