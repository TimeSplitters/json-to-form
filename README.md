# JSON to Form Bundle

**Author:** Christophe Abillama <christophe.abillama@gmail.com>

Symfony bundle to transform JSON structures into dynamic forms with Live Components.

## Installation

```bash
composer require timesplitters/json-to-form
```

Add the bundle in `config/bundles.php`:

```php
return [
    // ...
    TimeSplitters\JsonFormBundle\JsonFormBundle::class => ['all' => true],
];
```

## Quick Start

For quick usage, you only need **3 elements**:

### 1. 📋 A JSON structure that follows the format

```json
{
    "slug": "my-form",
    "sections": [
        {
            "slug": "information",
            "title": "Personal Information",
            "categories": [
                {
                    "slug": "identity",
                    "title": "Identity",
                    "questions": [
                        {
                            "key": "name",
                            "type": "text",
                            "label": "Name",
                            "required": true
                        },
                        {
                            "key": "email",
                            "type": "email",
                            "label": "Email",
                            "required": true
                        },
                        {
                            "key": "age",
                            "type": "integer",
                            "label": "Age"
                        }
                    ]
                }
            ],
            "submit": {
                "label": "Save Information",
                "class": "btn btn-success w-100 mt-3"
            }
        }
    ]
}
```

### 2. 🎛️ A Symfony controller that uses the service

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use TimeSplitters\JsonFormBundle\Service\JsonToFormTransformer;

class MyController extends AbstractController
{
    public function __construct(
        private JsonToFormTransformer $jsonToFormTransformer
    ) {}
    
    #[Route('/my-form', name: 'my_form')]
    public function index(Request $request): Response
    {
        // Your JSON structure (from file, database, etc.)
        $structure = [
            'slug' => 'my-form',
            'sections' => [
                [
                    'slug' => 'information',
                    'title' => 'Personal Information',
                    'categories' => [
                        [
                            'slug' => 'identity',
                            'title' => 'Identity',
                            'questions' => [
                                [
                                    'key' => 'name',
                                    'type' => 'text',
                                    'label' => 'Name',
                                    'required' => true
                                ],
                                [
                                    'key' => 'email',
                                    'type' => 'email',
                                    'label' => 'Email',
                                    'required' => true
                                ]
                            ]
                        ]
                    ],
                    'submit' => [
                        'label' => 'Save Information',
                        'class' => 'btn btn-success w-100 mt-3'
                    ]
                ]
            ]
        ];

        // Transform JSON to Symfony Form
        $form = $this->jsonToFormTransformer->transform($structure);
        $form->handleRequest($request);
        
        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Get form data as key-value array
            $data = $form->getData();
            
            // $data will contain: ['name' => 'John Doe', 'email' => 'john@example.com']
            // Process the data (save to database, send email, etc.)
            
            // Add success message
            $this->addFlash('success', 'Form submitted successfully!');
            
            // Redirect to avoid resubmission
            return $this->redirectToRoute('my_form');
        }

        return $this->render('my_form.html.twig', [
            'form' => $form,
        ]);
    }
}
```

### 3. 🎨 Simple rendering with `{{ form(form) }}`

```twig
{# templates/my_form.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}My JSON Form{% endblock %}

{% block body %}
    <div class="container">
        <h1>My JSON Form</h1>
        
        {{ form(form) }}
    </div>
{% endblock %}
```

## That's it! 🎉

With these 3 elements, you have a functional dynamic form generated from a JSON structure.

> **💡 Note:** You can configure submit buttons directly in your JSON structure at the section level with custom labels and CSS classes. If no submit is configured, the bundle automatically adds a default "Submit" button at the end of the form. Manage translations in your own application for maximum flexibility!

## Form Data Structure

When the form is submitted and valid, `$form->getData()` returns an associative array where:
- **Keys** are the `key` values from your JSON questions
- **Values** are the user-submitted values

**Example:**
```php
// JSON structure has questions with keys: 'name', 'email', 'age'
$data = $form->getData();

// $data will contain:
[
    'name' => 'John Doe',
    'email' => 'john@example.com', 
    'age' => 30
]
```

**Data processing:**
```php
if ($form->isSubmitted() && $form->isValid()) {
    $data = $form->getData();
    
    // Access individual values
    $userName = $data['name'];
    $userEmail = $data['email'];
    
    // Save to database, send emails, etc.
    $user = new User();
    $user->setName($userName);
    $user->setEmail($userEmail);
    $entityManager->persist($user);
    $entityManager->flush();
}
```

## Structure des données du formulaire

Lorsque le formulaire est soumis et valide, `$form->getData()` retourne un tableau associatif où :
- **Les clés** sont les valeurs `key` de vos questions JSON
- **Les valeurs** sont les valeurs saisies par l'utilisateur

**Exemple :**
```php
// La structure JSON a des questions avec les clés : 'nom', 'email', 'age'
$data = $form->getData();

// $data contiendra :
[
    'nom' => 'Jean Dupont',
    'email' => 'jean@example.com', 
    'age' => 30
]
```

**Traitement des données :**
```php
if ($form->isSubmitted() && $form->isValid()) {
    $data = $form->getData();
    
    // Accéder aux valeurs individuelles
    $nomUtilisateur = $data['nom'];
    $emailUtilisateur = $data['email'];
    
    // Sauvegarder en base, envoyer des emails, etc.
    $utilisateur = new User();
    $utilisateur->setNom($nomUtilisateur);
    $utilisateur->setEmail($emailUtilisateur);
    $entityManager->persist($utilisateur);
    $entityManager->flush();
}
```

## Supported Field Types

- `text` - Text field
- `email` - Email field
- `password` - Password field
- `integer` - Integer number field
- `number` - Decimal number field
- `choice` - Dropdown list or checkboxes
- `checkbox` - Checkbox
- `textarea` - Text area
- `date` - Date picker
- `tel` - Phone field
- `url` - URL field
- `search` - Search field
- `range` - Range slider
- `color` - Color picker
- `file` - File upload
- `hidden` - Hidden field

## Field Options

The options defined in the JSON directly correspond to the options of Symfony form field types. You can use any option supported by the corresponding field type.

**Example with advanced options:**

```json
{
    "key": "email",
    "type": "email",
    "label": "Email address",
    "required": true,
    "help": "We'll use this address to contact you",
    "placeholder": "example@domain.com",
    "attr": {
        "class": "custom-input",
        "data-validate": "true"
    },
    "constraints": {
        "Email": {
            "message": "This email address is not valid"
        },
        "Length": {
            "min": 5,
            "max": 180,
            "minMessage": "Email must contain at least {{ limit }} characters",
            "maxMessage": "Email cannot exceed {{ limit }} characters"
        }
    }
}
```

**Important:**
- The `key` and `type` keys are specific to the bundle and are not Symfony options
- The `key` defines the field name in the form
- The `type` defines the field type to use
- All other keys are passed directly as options to the Symfony field type

## Submit Button Configuration

You can configure submit buttons at the section level in your JSON structure:

```json
{
    "sections": [
        {
            "slug": "my-section",
            "title": "My Section",
            "categories": [...],
            "submit": {
                "label": "Save Section",
                "class": "btn btn-success w-100 mt-3",
                "attr": {
                    "data-confirm": "Are you sure?"
                }
            }
        }
    ]
}
```

**Submit options:**
- `label` - Button text (plain text, manage translations in your app)
- `class` - CSS classes for styling
- `attr` - Additional HTML attributes

**Default behavior:**
- If no section has a submit button configured, a default "Submit" button is added at the end
- Default label: "Submit" (customize in your JSON structure)
- Default CSS class: `btn btn-primary`

## Available Services

- **`JsonToFormTransformer`** - Main service to transform JSON to form
- **`FormGeneratorService`** - Advanced form construction
- **`QuestionConditionEvaluator`** - Visibility conditions management
- **`JsonPathHelper`** - JSONPath query utility
- **`FormStateManager`** - Session state management

---

# JSON to Form Bundle (Français)

**Auteur:** Christophe Abillama <christophe.abillama@gmail.com>

Bundle Symfony pour transformer des structures JSON en formulaires dynamiques avec Live Components.

## Installation

```bash
composer require timesplitters/json-to-form
```

Ajoutez le bundle dans `config/bundles.php` :

```php
return [
    // ...
    TimeSplitters\JsonFormBundle\JsonFormBundle::class => ['all' => true],
];
```

## Utilisation Rapide

Pour une utilisation rapide, il vous faut seulement **3 éléments** :

### 1. 📋 Une structure JSON qui respecte le format

```json
{
    "slug": "mon-formulaire",
    "sections": [
        {
            "slug": "informations",
            "title": "Informations personnelles",
            "categories": [
                {
                    "slug": "identite",
                    "title": "Identité",
                    "questions": [
                        {
                            "key": "nom",
                            "type": "text",
                            "label": "Nom",
                            "required": true
                        },
                        {
                            "key": "email",
                            "type": "email",
                            "label": "Email",
                            "required": true
                        },
                        {
                            "key": "age",
                            "type": "integer",
                            "label": "Âge"
                        }
                    ]
                }
            ],
            "submit": {
                "label": "Enregistrer les informations",
                "class": "btn btn-success w-100 mt-3"
            }
        }
    ]
}
```

### 2. 🎛️ Un contrôleur qui fait appel à `transform()`

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use TimeSplitters\JsonFormBundle\Service\JsonToFormTransformer;

class MyController extends AbstractController
{
    public function __construct(
        private JsonToFormTransformer $jsonToFormTransformer
    ) {}
    
    #[Route('/my-form', name: 'my_form')]
    public function index(Request $request): Response
    {
        // Your JSON structure (from file, database, etc.)
        $structure = [
            'slug' => 'mon-formulaire',
            'sections' => [
                [
                    'slug' => 'informations',
                    'title' => 'Informations personnelles',
                    'categories' => [
                        [
                            'slug' => 'identite',
                            'title' => 'Identité',
                            'questions' => [
                                [
                                    'key' => 'nom',
                                    'type' => 'text',
                                    'label' => 'Nom',
                                    'required' => true
                                ],
                                [
                                    'key' => 'email',
                                    'type' => 'email',
                                    'label' => 'Email',
                                    'required' => true
                                ]
                            ]
                        ]
                    ],
                    'submit' => [
                        'label' => 'Enregistrer les informations',
                        'class' => 'btn btn-success w-100 mt-3'
                    ]
                ]
            ]
        ];

        // Transform JSON to Symfony Form
        $form = $this->jsonToFormTransformer->transform($structure);
        $form->handleRequest($request);
        
        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {
            // Get form data as key-value array
            $data = $form->getData();
            
            // $data will contain: ['nom' => 'Jean Dupont', 'email' => 'jean@example.com']
            // Process the data (save to database, send email, etc.)
            
            // Add success message
            $this->addFlash('success', 'Formulaire soumis avec succès!');
            
            // Redirect to avoid resubmission
            return $this->redirectToRoute('my_form');
        }

        return $this->render('my_form.html.twig', [
            'form' => $form,
        ]);
    }
}
```

### 3. 🎨 Un rendu simple avec `{{ form(form) }}`

```twig
{# templates/my_form.html.twig #}
{% extends 'base.html.twig' %}

{% block title %}Mon Formulaire JSON{% endblock %}

{% block body %}
    <div class="container">
        <h1>Mon Formulaire JSON</h1>
        
        {{ form(form) }}
    </div>
{% endblock %}
```

## C'est tout ! 🎉

Avec ces 3 éléments, vous avez un formulaire dynamique fonctionnel généré depuis une structure JSON.

> **💡 Note :** Vous pouvez configurer les boutons de soumission directement dans votre structure JSON au niveau des sections avec des libellés et des classes CSS personnalisés. Si aucun bouton de soumission n'est configuré, le bundle ajoute automatiquement un bouton "Envoyer" par défaut à la fin du formulaire. Gérez les traductions dans votre propre application pour une flexibilité maximale !

## Types de champs supportés

- `text` - Champ texte
- `email` - Champ email
- `password` - Champ mot de passe
- `integer` - Champ nombre entier
- `number` - Champ nombre décimal
- `choice` - Liste déroulante ou cases à cocher
- `checkbox` - Case à cocher
- `textarea` - Zone de texte
- `date` - Sélecteur de date
- `tel` - Champ téléphone
- `url` - Champ URL
- `search` - Champ de recherche
- `range` - Curseur de valeur
- `color` - Sélecteur de couleur
- `file` - Upload de fichier
- `hidden` - Champ caché

## Options des champs

Les options définies dans le JSON correspondent directement aux options des types de champs Symfony. Vous pouvez utiliser toutes les options supportées par le type de champ correspondant.

**Exemple avec des options avancées :**

```json
{
    "key": "email",
    "type": "email",
    "label": "Adresse email",
    "required": true,
    "help": "Nous utiliserons cette adresse pour vous contacter",
    "placeholder": "exemple@domaine.com",
    "attr": {
        "class": "custom-input",
        "data-validate": "true"
    },
    "constraints": {
        "Email": {
            "message": "Cette adresse email n'est pas valide"
        },
        "Length": {
            "min": 5,
            "max": 180,
            "minMessage": "L'email doit contenir au moins {{ limit }} caractères",
            "maxMessage": "L'email ne peut pas dépasser {{ limit }} caractères"
        }
    }
}
```

**Important :**
- Les clés `key` et `type` sont spécifiques au bundle et ne sont pas des options Symfony
- La clé `key` définit le nom du champ dans le formulaire
- La clé `type` définit le type de champ à utiliser
- Toutes les autres clés sont transmises directement comme options au type de champ Symfony

## Configuration du bouton de soumission

Vous pouvez configurer les boutons de soumission au niveau des sections dans votre structure JSON :

```json
{
    "sections": [
        {
            "slug": "ma-section",
            "title": "Ma Section",
            "categories": [...],
            "submit": {
                "label": "Enregistrer la section",
                "class": "btn btn-success w-100 mt-3",
                "attr": {
                    "data-confirm": "Êtes-vous sûr ?"
                }
            }
        }
    ]
}
```

**Options de soumission :**
- `label` - Texte du bouton (texte brut, gérer les traductions dans votre application)
- `class` - Classes CSS pour la mise en forme
- `attr` - Attributs HTML supplémentaires

**Comportement par défaut :**
- Si aucune section n'a de bouton de soumission configuré, un bouton "Envoyer" par défaut est ajouté à la fin
- Le libellé par défaut : "Envoyer" (personnalisez-le dans votre structure JSON)
- La classe CSS par défaut : `btn btn-primary`

## Services disponibles

- **`JsonToFormTransformer`** - Service principal pour transformer JSON en formulaire
- **`FormGeneratorService`** - Construction avancée de formulaires
- **`QuestionConditionEvaluator`** - Gestion des conditions de visibilité
- **`JsonPathHelper`** - Utilitaire pour requêtes JSONPath
- **`FormStateManager`** - Gestion d'état en session

## Licence

MIT - Christophe Abillama
