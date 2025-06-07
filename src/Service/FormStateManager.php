<?php

namespace TimeSplitters\JsonFormBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Gestion simplifiée de l'état du formulaire.
 * 
 * @author Christophe Abillama <christophe.abillama@gmail.com>
 */
final class FormStateManager
{
    public function __construct(private RequestStack $stack) {}

    /** Récupère la structure active du formulaire depuis la session */
    public function structure(): array
    {
        return $this->stack->getSession()->get('active_form_structure', []);
    }

    /** Récupère les données courantes du formulaire (brouillon) */
    public function data(): array
    {
        return $this->stack->getSession()->get('checkout_draft', []);
    }

    /** Fusionne et sauvegarde des données dans la session */
    public function save(array $data): void
    {
        $this->stack->getSession()->set('checkout_draft', array_merge($this->data(), $data));
    }
    
    /** Définit la structure du formulaire */
    public function setStructure(array $structure): void
    {
        $this->stack->getSession()->set('active_form_structure', $structure);
    }
    
    /** Réinitialise les données du formulaire */
    public function reset(): void
    {
        $this->stack->getSession()->remove('checkout_draft');
        $this->stack->getSession()->remove('active_form_structure');
    }
}
