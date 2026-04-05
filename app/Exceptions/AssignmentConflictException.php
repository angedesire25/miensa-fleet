<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Levée par AssignmentService quand une affectation ne peut pas être créée.
 *
 * Types de conflit possibles :
 *   'vehicle'     → le véhicule est déjà occupé sur ce créneau
 *   'driver'      → le chauffeur a une affectation qui chevauche ce créneau
 *   'eligibility' → le chauffeur n'est pas éligible (permis, visite médicale…)
 */
class AssignmentConflictException extends RuntimeException
{
    public function __construct(
        private readonly string $conflictType,
        private readonly array  $reasons = [],
        string                  $message = '',
        int                     $code    = 0,
        ?\Throwable             $previous = null,
    ) {
        parent::__construct(
            $message ?: $this->buildMessage(),
            $code,
            $previous
        );
    }

    public function getConflictType(): string
    {
        return $this->conflictType;
    }

    /** Liste des raisons lisibles, destinée à l'affichage dans les formulaires */
    public function getReasons(): array
    {
        return $this->reasons;
    }

    private function buildMessage(): string
    {
        return match ($this->conflictType) {
            'vehicle'     => 'Le véhicule est déjà affecté sur ce créneau.',
            'driver'      => 'Le chauffeur a déjà une affectation qui chevauche ce créneau.',
            'eligibility' => 'Le chauffeur n\'est pas éligible : ' . implode(' | ', $this->reasons),
            default       => 'Conflit d\'affectation.',
        };
    }
}
