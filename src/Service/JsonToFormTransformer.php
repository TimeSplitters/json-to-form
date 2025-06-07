<?php

namespace TimeSplitters\JsonFormBundle\Service;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use TimeSplitters\JsonFormBundle\Service\FormGeneratorService;

/**
 * Service principal pour transformer une structure JSON en formulaire Symfony
 * 
 * Usage simple :
 * $form = $this->jsonToForm->transform($structure, $data, $builder);
 * 
 * @author Christophe Abillama <christophe.abillama@gmail.com>
 */
class JsonToFormTransformer
{
    public function __construct(
        private FormGeneratorService $formGenerator,
    ) {}

    /**
     * Transforme une structure JSON en formulaire Symfony
     * 
     * @param array $structure Structure JSON du formulaire
     * @param array $data Données initiales
     * @param FormBuilderInterface $builder Builder existant
     * @return FormInterface Le formulaire généré
     */
    public function transform(array $structure, array $data, FormBuilderInterface $builder): FormInterface
    {
        $builder->setData($data);
        
        return $this->formGenerator->buildForm($builder, $structure, $data);
    }
}
