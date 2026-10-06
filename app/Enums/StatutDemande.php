<?php

namespace App\Enums;

enum StatutDemande: string
{
    case Deposee = 'deposee';
    case EnCours = 'en_cours';
    case Validee = 'validee';
    case Rejetee = 'rejetee';

    /** @return array<int, self> */
    public function transitionsPossibles(): array
    {
        return match ($this) {
            self::Deposee => [self::EnCours],
            self::EnCours => [self::Validee, self::Rejetee],
            self::Validee, self::Rejetee => [],
        };
    }

    public function peutPasserA(self $cible): bool
    {
        return in_array($cible, $this->transitionsPossibles(), true);
    }

    public function estFinal(): bool
    {
        return $this->transitionsPossibles() === [];
    }
}