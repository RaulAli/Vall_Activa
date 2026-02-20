<?php
declare(strict_types=1);

namespace App\Presentation\Http\Request\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 72)]
    public string $password = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Assert\Regex(pattern: '/^[a-z0-9\-_]+$/i', message: 'Slug may only contain letters, numbers, hyphens and underscores.')]
    public string $slug = '';
}
