<?php

namespace TimeSplitters\JsonFormBundle\Service;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Service de génération de formulaires Symfony à partir de structures JSON
 * 
 * @author Christophe Abillama <christophe.abillama@gmail.com>
 */
class FormGeneratorService
{
    public function __construct(
        private QuestionConditionEvaluator $conditionEvaluator, 
        private PropertyAccessorInterface $propertyAccessor,
        private JsonPathHelper $jsonPathHelper
    ) {
    }
    
    /**
     * Construit dynamiquement un formulaire Symfony à partir d'une structure JSON.
     * 
     * @param FormBuilderInterface $builder Le builder de formulaire
     * @param array $structure La structure JSON du formulaire
     * @param array $externalData Données externes qui peuvent influencer les dépendances (valeurs des sections précédentes)
     * @return FormInterface Le formulaire construit
     */
    public function buildForm(FormBuilderInterface $builder, array $structure, array $externalData = []): FormInterface
    {
        $dynBuilder = new DynamicFormBuilder($builder);

        if (!isset($structure['sections']) || !isset($structure['slug'])) {
            throw new \InvalidArgumentException("La structure JSON du formulaire doit contenir au moins une section et un champ 'slug'.");
        }

        // Récupérer les données actuelles du formulaire pour les dépendances
        $formData = $builder->getData() ?? [];

        $hasSubmitButton = false;

        foreach ($structure['sections'] as $section) {
            if (!isset($section['slug']) || !is_string($section['slug']) || empty(trim($section['slug']))) {
                throw new \InvalidArgumentException("Chaque section doit avoir un 'slug' (chaîne non vide).");
            }
            $sectionKey      = $section['slug'];
            $sectionLabel    = $section['title'] ?? ucfirst($sectionKey);

            // Builder de section (inherit_data=true pour ne pas créer de niveau de données supplémentaire)
            $sectionBuilder = $dynBuilder->create($sectionKey, FormType::class, [
                'label'        => $sectionLabel,
                'inherit_data' => true,
            ]);

            foreach ($section['categories'] as $index => $category) {
                if (!isset($category['slug']) || !is_string($category['slug']) || empty(trim($category['slug']))) {
                    throw new \InvalidArgumentException(sprintf("Chaque catégorie dans la section '%s' doit avoir un 'slug' (chaîne non vide).", $sectionKey));
                }
                $categoryKey   = $category['slug'];
                $categoryLabel = $category['title'] ?? ucfirst($categoryKey);

                // Builder de catégorie (inherit_data=true pour ne pas créer de niveau de données supplémentaire)
                $categoryBuilder = $sectionBuilder->create($categoryKey, FormType::class, [
                    'label'        => $categoryLabel,
                    'inherit_data' => true,
                ]);

                if (isset($category['questions']) && is_array($category['questions'])) {
                    foreach ($category['questions'] as $question) {
                        $this->addQuestion($categoryBuilder, $question, $externalData, $formData);
                    }
                }

                $sectionBuilder->add($categoryBuilder);
            }

            // Ajouter automatiquement un bouton submit à la fin de la section si configuré
            if (isset($section['submit']) && is_array($section['submit'])) {
                $submitLabel = $section['submit']['label'] ?? 'Send';
                
                $submitAttrs = ['class' => 'btn btn-primary'];
                if (isset($section['submit']['class'])) {
                    $submitAttrs['class'] = $section['submit']['class'];
                }
                if (isset($section['submit']['attr']) && is_array($section['submit']['attr'])) {
                    $submitAttrs = array_merge($submitAttrs, $section['submit']['attr']);
                }
                
                $sectionBuilder->add('submit', SubmitType::class, [
                    'label' => $submitLabel,
                    'attr' => $submitAttrs
                ]);
                
                $hasSubmitButton = true;
            }

            $dynBuilder->add($sectionBuilder);
        }

        // Ajouter un bouton submit par défaut à la fin si aucune section n'en a configuré
        if (!$hasSubmitButton) {
            $dynBuilder->add('submit', SubmitType::class, [
                'label' => 'Send',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
        }

        return $dynBuilder->getForm();
    }

    /**
     * Ajoute une question au formulaire en gérant ses dépendances
     * 
     * @param FormBuilderInterface $builder Le builder de formulaire
     * @param array $question La configuration de la question
     * @param array $externalData Données externes qui peuvent influencer les dépendances
     * @param array $formData Données du formulaire pour les dépendances
     */
    public function addQuestion(FormBuilderInterface $builder, array $question, array $externalData = [], array $formData = []): void
    {
        if (!isset($question['key']) || !isset($question['type'])) {
            throw new \InvalidArgumentException("Chaque question doit avoir au moins une 'key' et un 'type'.");
        }

        $fieldName = $question['key'];
        unset($question['key']);
        $type      = $this->mapType($question['type']);
        unset($question['type']);
        
        $options = $question;
        // S'assurer que les options de base sont définies
        $options['label']    = $question['label'] ?? ucfirst($fieldName);
        $options['required'] = $question['required'] ?? false;

        // Gestion de la valeur par défaut
        if (isset($question['default'])) {
            $value = $question['default'];
            
            // Adapter la valeur selon le type de champ
            switch ($type) {
                case IntegerType::class:
                    $options['data'] = is_numeric($value) ? (int) $value : $value;
                    break;
                    
                case NumberType::class:
                    $options['data'] = is_numeric($value) ? (float) $value : $value;
                    break;
                    
                case FileType::class:
                    if ($value instanceof \Symfony\Component\HttpFoundation\File\File) {
                        $options['data'] = $value;
                    }
                    break;
                
                case DateTimeType::class:
                case DateType::class:
                    $options['data'] = new \DateTime($value);
                    break;
                
                case TimeType::class:
                    $options['data'] = new \DateTime($value);
                    break;
                default:
                    $options['data'] = $value;
            }
        }

        if (isset($question['choices'])) {
            $choices = $question['choices'];
            $preparedChoices = $this->isAssocArray($choices) ? array_flip($choices) : array_combine($choices, $choices);
            switch ($type) {
                case ChoiceType::class:
                case RadioType::class:
                case CheckboxType::class:
                    $options['choices'] = $preparedChoices;
                    break;
            }
        }

        if (isset($question['constraints'])) {
            $constraints = $question['constraints'];
            $options['constraints'] = $this->buildConstraints($constraints);
        }

        $dependencies = $question['displayDependencies'] ?? [];
        unset($options['displayDependencies']);
        if (!empty($dependencies) && isset($dependencies['operator'], $dependencies['conditions'])) {
            if($this->conditionEvaluator->shouldDisplay($dependencies, $formData, $externalData)){
                $builder->add($fieldName, $type, $options);
            }
        } else {
            $builder->add($fieldName, $type, $options);
        }
    }

    private function buildConstraints(array $constraintsConfig): array
    {
        $constraints = [];

        foreach ($constraintsConfig as $constraintName => $options) {
            $fqcn = 'Symfony\\Component\\Validator\\Constraints\\' . $constraintName;

            if (!class_exists($fqcn)) {
                throw new \InvalidArgumentException("Constraint class $fqcn does not exist.");
            }

            if (!is_subclass_of($fqcn, Constraint::class)) {
                throw new \InvalidArgumentException("$fqcn is not a valid Symfony Constraint.");
            }
            
            // Pour les contraintes Length, NotBlank, etc., on passe directement les options
            // sans utiliser OptionsResolver qui peut être trop strict
            if (is_array($options)) {
                // On passe directement le tableau associatif au constructeur
                $constraints[] = new $fqcn($options);
            } else {
                // Pour les contraintes sans options
                $constraints[] = new $fqcn();
            }
        }

        return $constraints;
    }

    private function mapType(string $type): string
    {
        return match ($type) {
            'text'      => TextType::class,
            'email'     => EmailType::class,
            'number',
            'integer'   => IntegerType::class,
            'date'      => DateType::class,
            'datetime'  => DateTimeType::class,
            'time'      => TimeType::class,
            'url'       => UrlType::class,
            'tel'       => TelType::class,
            'search'    => SearchType::class,
            'password'  => PasswordType::class,
            'range'     => RangeType::class,
            'percent'   => PercentType::class,
            'money'     => MoneyType::class,
            'country'   => CountryType::class,
            'language'  => LanguageType::class,
            'locale'    => LocaleType::class,
            'currency'  => CurrencyType::class,
            'checkbox'  => CheckboxType::class,
            'radio'     => RadioType::class,
            'choice'    => ChoiceType::class,
            'file'      => FileType::class,
            'collection'=> CollectionType::class,
            default     => TextType::class,
        };
    }

    private function isAssocArray(array $array): bool
    {
        return array_keys($array) !== range(0, \count($array) - 1);
    }
}
