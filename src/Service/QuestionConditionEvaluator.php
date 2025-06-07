<?php

namespace TimeSplitters\JsonFormBundle\Service;

/**
 * Service spécialisé dans l'évaluation des conditions de visibilité des questions
 * 
 * @author Christophe Abillama <christophe.abillama@gmail.com>
 */
final class QuestionConditionEvaluator
{
    public function __construct(private JsonPathHelper $jsonPathHelper)
    {
    }
    
    /**
     * Évalue si une question doit être affichée selon ses dépendances
     * 
     * @param array $dependencies Configuration des dépendances de la question
     * @param array $formData Données actuelles du formulaire
     * @param array $externalData Données externes (valeurs des sections précédentes)
     * @return bool True si la question doit être affichée, false sinon
     */
    public function shouldDisplay(array $dependencies, array $formData, array $externalData = []): bool
    {
        // Fusionner les données du formulaire avec les données externes
        // Les données du formulaire ont priorité sur les données externes en cas de conflit
        $allData = array_merge($externalData, $formData);
        // Si pas de dépendances, la question est toujours affichée
        if (empty($dependencies)) {
            return true;
        }
        
        // Structure avec opérateurs logiques
        $operator = $dependencies['operator'] ?? 'AND';
        $conditions = $dependencies['conditions'] ?? [];
        
        if (empty($conditions)) {
            return true;
        }
        return match ($operator) {
            'AND' => $this->evaluateAndConditions($conditions, $allData),
            'OR'  => $this->evaluateOrConditions($conditions, $allData),
            'NOT' => !$this->evaluateAndConditions($conditions, $allData),
            default => throw new \InvalidArgumentException("Opérateur logique non supporté: $operator")
        };
    }
    
    /**
     * Évalue une série de conditions avec l'opérateur AND
     */
    private function evaluateAndConditions(array $conditions, array $formData): bool
    {
        foreach ($conditions as $condition) {
            if (!$this->evaluateSingleCondition($condition, $formData)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Évalue une série de conditions avec l'opérateur OR
     */
    private function evaluateOrConditions(array $conditions, array $formData): bool
    {
        foreach ($conditions as $condition) {
            if ($this->evaluateSingleCondition($condition, $formData)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Évalue une condition unique
     */
    private function evaluateSingleCondition(array $condition, array $formData): bool
    {
        $field = $condition['field'] ?? null;
        if ($field === null) {
            return false;
        }
        
        // Recherche récursive de la valeur dans le tableau
        $value = $this->jsonPathHelper->findValueByKey($formData, $field);
        
        // Support des conditions imbriquées avec opérateurs
        if (isset($condition['operator']) && isset($condition['conditions'])) {
            return $this->shouldDisplay($condition, $formData, $formData);
        }
        
        // Types de conditions supportés
        return match (true) {
            isset($condition['isNotNull']) => $this->isNotNull($value),
            isset($condition['isNull']) => $this->isNull($value),
            isset($condition['hasValue']) => $this->equals($value, $condition['hasValue']),
            isset($condition['equals'])  => $this->equals($value, $condition['equals']),
            isset($condition['notEquals']) => !$this->equals($value, $condition['notEquals']),
            isset($condition['in']) => $this->in($value, $condition['in']),
            isset($condition['notIn']) => !$this->in($value, $condition['notIn']),
            isset($condition['contains']) => $this->contains($value, $condition['contains']),
            isset($condition['notContains']) => !$this->contains($value, $condition['contains']),
            isset($condition['greaterThan']) => $this->greaterThan($value, $condition['greaterThan']),
            isset($condition['lessThan']) => $this->lessThan($value, $condition['lessThan']),
            isset($condition['greaterThanOrEqual']) => $this->greaterThanOrEqual($value, $condition['greaterThanOrEqual']),
            isset($condition['lessThanOrEqual']) => $this->lessThanOrEqual($value, $condition['lessThanOrEqual']),
            default => false
        };
    }
    
    /**
     * Vérifie si une valeur n'est pas nulle ou vide
     */
    private function isNotNull(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        
        if (is_array($value) && count($value) === 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Vérifie si une valeur est nulle ou vide
     */
    private function isNull(mixed $value): bool
    {
        return !$this->isNotNull($value);
    }
    
    /**
     * Vérifie si une valeur est égale à une autre
     */
    private function equals(mixed $value, mixed $target): bool
    {
        return $value === $target;
    }
    
    /**
     * Vérifie si une valeur est dans un tableau
     */
    private function in(mixed $value, array $values): bool
    {
        return in_array($value, $values, true);
    }
    
    /**
     * Vérifie si un tableau contient une valeur spécifique
     * Ou si une chaîne contient une sous-chaîne
     */
    private function contains(mixed $value, mixed $target): bool
    {
        if (is_array($value)) {
            return in_array($target, $value, true);
        }
        
        if (is_string($value) && is_string($target)) {
            return str_contains($value, $target);
        }
        
        return false;
    }
    
    /**
     * Vérifie si une valeur est supérieure à une autre
     */
    private function greaterThan(mixed $value, mixed $target): bool
    {
        if (!is_numeric($value) || !is_numeric($target)) {
            return false;
        }
        
        return $value > $target;
    }
    
    /**
     * Vérifie si une valeur est inférieure à une autre
     */
    private function lessThan(mixed $value, mixed $target): bool
    {
        if (!is_numeric($value) || !is_numeric($target)) {
            return false;
        }
        
        return $value < $target;
    }
    
    /**
     * Vérifie si une valeur est supérieure ou égale à une autre
     */
    private function greaterThanOrEqual(mixed $value, mixed $target): bool
    {
        if (!is_numeric($value) || !is_numeric($target)) {
            return false;
        }
        
        return $value >= $target;
    }
    
    /**
     * Vérifie si une valeur est inférieure ou égale à une autre
     */
    private function lessThanOrEqual(mixed $value, mixed $target): bool
    {
        if (!is_numeric($value) || !is_numeric($target)) {
            return false;
        }
        
        return $value <= $target;
    }
}
