<?php

namespace App\Services\PocketBase\Exceptions;

class PocketBaseValidationException extends PocketBaseException
{
    private array $errors = [];

    public function withErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
