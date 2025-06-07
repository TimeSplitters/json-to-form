<?php

namespace TimeSplitters\JsonFormBundle\Service;

use Symfony\Component\JsonPath\JsonCrawler;

/**
 * Service utilitaire pour manipuler des données JSON avec JSONPath
 * 
 * @author Christophe Abillama <christophe.abillama@gmail.com>
 */
class JsonPathHelper
{
    /**
     * Trouve une valeur dans un tableau multidimensionnel par sa clé, peu importe sa position
     * 
     * @param array $data Le tableau dans lequel chercher
     * @param string $key La clé à rechercher
     * @return mixed La valeur trouvée ou null si non trouvée
     */
    public function findValueByKey(array $data, string $key): mixed
    {
        // Convertir le tableau en JSON pour utiliser JsonCrawler
        $json = json_encode($data);
        if ($json === false) {
            return null;
        }
        
        try {
            // Utiliser JsonCrawler pour trouver toutes les occurrences de la clé
            // La syntaxe $..["key"] recherche la clé à n'importe quel niveau du document
            $crawler = new JsonCrawler($json);
            
            // Construire l'expression JSONPath avec la clé
            // Format : $..['key'] pour éviter les problèmes d'échappement
            $expression = sprintf("$..['%s']", $key);
            
            // Exécuter la requête JSONPath
            $results = $crawler->find($expression);
            
            // Retourner la première valeur trouvée ou null
            return $results[0] ?? null;
        } catch (\Exception $e) {
            // En cas d'erreur de syntaxe JSONPath, retourner null
            return null;
        }
    }

    /**
     * Récupère les chemins JSONPath complets des questions dans une section donnée du formulaire
     *
     * @param array  $data       La structure JSON du formulaire
     * @param string $sectionKey La clé de la section
     * @return array             Liste des chemins JSONPath complets (section.categorie.questionKey)
     */
    public function findQuestionsPath(array $data, string $sectionKey): array
    {
        $json = json_encode($data);
        if ($json === false) {
            return [];
        }
        try {
            $crawler    = new JsonCrawler($json);
            $expression = sprintf("$..sections[?(@.slug=='%s')]..questions[*].key", $sectionKey);
            $results    = $crawler->find($expression);
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Recherche une valeur dans un JSON en utilisant une expression JSONPath
     *
     * @param array  $data       Les données JSON sous forme de tableau
     * @param string $jsonPath   L'expression JSONPath (ex: "informations.civiles.civility")
     * @return mixed             La valeur trouvée ou null si non trouvée
     */
    public function findValueByJsonPath(array $data, string $jsonPath): mixed
    {
        $json = json_encode($data);
        if ($json === false) {
            return null;
        }

        try {
            $crawler = new JsonCrawler($json);
            // Construire l'expression JSONPath complète
            $expression = '$.' . $jsonPath;
            $results = $crawler->find($expression);
            
            // Retourner la première valeur trouvée ou null
            return $results[0] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
