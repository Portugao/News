<?php

/**
 * News.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepages-mit-zikula.de>.
 *
 * @see https://homepages-mit-zikula.de
 * @see https://ziku.la
 *
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

declare(strict_types=1);

namespace MU\NewsModule\Form\Type\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Multi upload field type implementation class.
 */
class ImagesInArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            foreach (['file'] as $uploadFieldName) {
                $entity[$uploadFieldName] = [
                    $uploadFieldName => $entity[$uploadFieldName] instanceof File
                        ? $entity[$uploadFieldName]->getPathname()
                        : null
                ];
            }
        });
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $entity = $event->getData();
            foreach (['file'] as $uploadFieldName) {
                if (is_array($entity[$uploadFieldName])) {
                    $entity[$uploadFieldName] = $entity[$uploadFieldName][$uploadFieldName];
                }
            }
        });
        */

        $builder->add('file', UploadType::class, [
            'label' => 'file',
            'attr' => [
                'class' => ' validate-upload',
            ],
            'required' => false && 'create' === $options['mode'],
            'entity' => $options['entity'],
            'allowed_extensions' => 'gif, jpeg, jpg, png',
            'allowed_size' => $options['allowed_size'],
        ]);
        $builder->add('copyright', TextType::class, [
            'label' => 'copyright',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['mode', 'entity'])
            ->setDefined(['allowed_extensions', 'allowed_size'])
            ->setDefaults([
                'attr' => [
                    'class' => 'file-selector',
                ],
                'allowed_extensions' => '',
                'allowed_size' => '',
                'error_bubbling' => false,
            ])
            ->setAllowedTypes('allowed_extensions', 'string')
            ->setAllowedTypes('allowed_size', 'string')
        ;
    }

    public function getBlockPrefix()
    {
        return 'munewsmodule_field_imagesinarticle';
    }
}
