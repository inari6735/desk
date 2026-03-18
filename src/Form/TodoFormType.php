<?php

namespace App\Form;

use App\Entity\TaskGroup;
use App\Entity\Todo;
use App\Entity\User;
use App\Enum\TodoStatus;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Entity\Project;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TodoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('status', EnumType::class, [
                'class' => TodoStatus::class,
                'choice_label' => fn (TodoStatus $s) => $s->label(),
            ])
            ->add('dueDate', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('estimatedHours', NumberType::class, [
                'required' => false,
                'label' => 'Estimate (hours)',
                'scale' => 1,
                'attr' => ['step' => '0.5', 'min' => '0'],
                'html5' => true,
            ])
            ->add('spentHours', NumberType::class, [
                'required' => false,
                'label' => 'Spent (hours)',
                'scale' => 1,
                'attr' => ['step' => '0.5', 'min' => '0'],
                'html5' => true,
            ])
            ->add('taskGroups', EntityType::class, [
                'class' => TaskGroup::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'by_reference' => false,
                'label' => 'Groups',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('g')->orderBy('g.position', 'ASC');
                    if ($options['project']) {
                        $qb->where('g.project = :project')->setParameter('project', $options['project']);
                    }
                    return $qb;
                },
            ])
            ->add('assignedTo', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'required' => false,
                'placeholder' => 'Unassigned',
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('u')->orderBy('u.email', 'ASC'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
            'project' => null,
        ]);

        $resolver->setAllowedTypes('project', ['null', Project::class]);
    }
}
