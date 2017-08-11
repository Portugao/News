<?php
/**
 * News.
 *
 * @copyright Michael Ueberschaer (MU)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Michael Ueberschaer <info@homepage-mit-zikula.de>.
 * @link https://homepages-mit-zikula.de
 * @link http://zikula.org
 * @version Generated by ModuleStudio 1.0.1 (https://modulestudio.de).
 */

namespace MU\NewsModule\Form\Type\Hook\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\IdentityTranslator;

/**
 * Delete message form type base class.
 */
abstract class AbstractDeleteMessageType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];
        $builder
            ->add('dummyName', TextType::class, [
                'label' => $translator->__('Dummy message text'),
                'required' => true
            ])
            ->add('dummmyChoice', ChoiceType::class, [
                'label' => $translator->__('Dummy message choice'),
                'choices' => [
                    $translator->__('Option A') => 'A',
                    $translator->__('Option A') => 'B',
                    $translator->__('Option A') => 'C'
                ],
                'choices_as_values' => true,
                'required' => true,
                'multiple' => true,
                'expanded' => true
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'munewsmodule_hook_deletemessage';
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => new IdentityTranslator()
        ]);
    }
}
