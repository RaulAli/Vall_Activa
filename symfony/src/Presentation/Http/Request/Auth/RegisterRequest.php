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
    #[Assert\Choice(choices: ['ROLE_BUSINESS', 'ROLE_ATHLETE', 'ROLE_GUIDE'])]
    public string $role = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Assert\Regex(pattern: '/^[a-z0-9\-_]+$/i', message: 'Slug may only contain letters, numbers, hyphens and underscores.')]
    public string $slug = '';

    #[Assert\Length(max: 2000)]
    public ?string $bio = null;

    #[Assert\Length(max: 255)]
    public ?string $city = null;

    /** @var list<string>|null */
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Length(max: 32),
        new Assert\Regex(pattern: '/^[a-z0-9_\-]+$/i', message: 'Each sport code must be alphanumeric, underscore or hyphen.'),
    ])]
    public ?array $sports = null;
}
